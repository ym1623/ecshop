<?php

/**
 * ECSHOP 程序说明
 * ===========================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: sxc_shop $
 * $Id: affiliate_ck.php 17093 2010-04-12 05:04:53Z sxc_shop $
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');

admin_priv('affiliate_ck');
$timestamp = time();

$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
empty($affiliate) && $affiliate = array();
$separate_on = $affiliate['on'];
/*------------------------------------------------------ */
//-- 分成页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $logdb = get_affiliate_ck();
    $smarty->assign('full_page',  1);
    $smarty->assign('ur_here', $_LANG['affiliate_ck']);
    $smarty->assign('on', $separate_on);
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);
    if (!empty($_GET['auid']))
    {
        $smarty->assign('action_link',  array('text' => $_LANG['back_note'], 'href'=>"users.php?act=edit&id=$_GET[auid]"));
    }
    assign_query_info();
    $smarty->display('affiliate_ck_list.htm');
}
/*------------------------------------------------------ */
//-- 分页
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $logdb = get_affiliate_ck();
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('on', $separate_on);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);

    $sort_flag  = sort_flag($logdb['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('affiliate_ck_list.htm'), '', array('filter' => $logdb['filter'], 'page_count' => $logdb['page_count']));
}
/*
    取消分成，不再能对该订单进行分成
*/
elseif ($_REQUEST['act'] == 'del')
{
    $oid = (int)$_REQUEST['oid'];
    $stat = $db->getOne("SELECT is_separate FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$oid'");
    if (empty($stat))
    {
        $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') .
               " SET is_separate = 2" .
               " WHERE order_id = '$oid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    撤销某次分成，将已分成的收回来
*/
elseif ($_REQUEST['act'] == 'rollback')
{
    $logid = (int)$_REQUEST['logid'];
    $stat = $db->getRow("SELECT * FROM " . $GLOBALS['ecs']->table('affiliate_log') . " WHERE log_id = '$logid'");
    if (!empty($stat))
    {
        if($stat['separate_type'] == 1)
        {
            //推荐订单分成
            $flag = -2;
        }
        else
        {
            //推荐注册分成
            $flag = -1;
        }
        log_account_change($stat['user_id'], -$stat['money'], 0, -$stat['point'], 0, $_LANG['loginfo']['cancel']);
        $sql = "UPDATE " . $GLOBALS['ecs']->table('affiliate_log') .
               " SET separate_type = '$flag'" .
               " WHERE log_id = '$logid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
/*
    分成
*/
elseif ($_REQUEST['act'] == 'separate')
{
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($affiliate) && $affiliate = array();

    $separate_by = $affiliate['config']['separate_by'];

    $oid = (int)$_REQUEST['oid'];

    $row = $db->getRow("SELECT o.order_sn, o.is_separate, (o.goods_amount - o.discount) AS goods_amount, o.user_id FROM " . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('users') . " u ON o.user_id = u.user_id".
            " WHERE order_id = '$oid'");

    $order_sn = $row['order_sn'];

    if (empty($row['is_separate']))
    {
        $affiliate['config']['level_point_all'] = (float)$affiliate['config']['level_point_all'];
        $affiliate['config']['level_money_all'] = (float)$affiliate['config']['level_money_all'];
        if ($affiliate['config']['level_point_all'])
        {
            $affiliate['config']['level_point_all'] /= 100;
        }
        if ($affiliate['config']['level_money_all'])
        {
            $affiliate['config']['level_money_all'] /= 100;
        }
        //$money = round($affiliate['config']['level_money_all'] * $row['goods_amount'],2);
        //$integral = integral_to_give(array('order_id' => $oid, 'extension_code' => ''));
        //$point = round($affiliate['config']['level_point_all'] * intval($integral['rank_points']), 0);

        if(empty($separate_by))
        {
            //推荐注册分成
            $num = count($affiliate['item']);
            for ($i=0; $i < $num; $i++)
            {
                $affiliate['item'][$i]['level_point'] = (float)$affiliate['item'][$i]['level_point'];
                $affiliate['item'][$i]['level_money'] = (float)$affiliate['item'][$i]['level_money'];
                if ($affiliate['item'][$i]['level_point'])
                {
                    $affiliate['item'][$i]['level_point'] /= 100;
                }
                if ($affiliate['item'][$i]['level_money'])
                {
                    $affiliate['item'][$i]['level_money'] /= 100;
                }
                $setmoney = round($money * $affiliate['item'][$i]['level_money'], 2);
                $setpoint = round($point * $affiliate['item'][$i]['level_point'], 0);
                $row = $db->getRow("SELECT o.parent_id as user_id,u.user_name FROM " . $GLOBALS['ecs']->table('users') . " o" .
                        " LEFT JOIN" . $GLOBALS['ecs']->table('users') . " u ON o.parent_id = u.user_id".
                        " WHERE o.user_id = '$row[user_id]'"
                    );
                $up_uid = $row['user_id'];
                if (empty($up_uid) || empty($row['user_name']))
                {
                    break;
                }
                else
                {
                    $info = sprintf($_LANG['separate_info'], $order_sn, $setmoney, $setpoint);
                    log_account_change($up_uid, $setmoney, 0, $setpoint, 0, $info);
                    write_affiliate_log($oid, $up_uid, $row['user_name'], $setmoney, $setpoint, $separate_by);
                }
            }
        }
        else
        {
            //推荐订单分成
            $row = $db->getRow("SELECT o.parent_id, u.user_name,o.extension_id FROM " . $GLOBALS['ecs']->table('order_info') . " o" .
                    " LEFT JOIN" . $GLOBALS['ecs']->table('users') . " u ON o.parent_id = u.user_id".
                    " WHERE o.order_id = '$oid'"
                );
            $up_uid = $row['parent_id'];
			$sql = 'SELECT goods_rebate FROM '. $GLOBALS['ecs']->table('group_activity') . " WHERE group_id='$row[extension_id]'";  
		    $money =  $GLOBALS['db']->getOne($sql);
            if(!empty($up_uid) && $up_uid > 0 && $money > 0)
            {
                //$info = sprintf($_LANG['separate_info'], $order_sn, $money, $point);
				 $info = sprintf('邀请返利', $order_sn , $money,0);
                log_account_change($up_uid, $money, 0, $point, 0, $info);
                write_affiliate_log($oid, $up_uid, $row['user_name'], $money, $point, $separate_by);
            }
            else
            {
                $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
                sys_msg($_LANG['edit_fail'], 1 ,$links);
            }
        }
        $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') .
               " SET is_separate = 1" .
               " WHERE order_id = '$oid'";
        $db->query($sql);
    }
    $links[] = array('text' => $_LANG['affiliate_ck'], 'href' => 'affiliate_ck.php?act=list');
    sys_msg($_LANG['edit_ok'], 0 ,$links);
}
function get_affiliate_ck()
{

    $affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
    empty($affiliate) && $affiliate = array();
    $separate_by = $affiliate['config']['separate_by'];

    $sqladd =  " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
	           " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
			   " AND o.extension_code='group_buy'";
    if (isset($_REQUEST['status']))
    {
        $sqladd = ' AND o.is_separate = ' . (int)$_REQUEST['status'];
        $filter['status'] = (int)$_REQUEST['status'];
    }
    if (isset($_REQUEST['order_sn']))
    {
        $sqladd = ' AND o.order_sn LIKE \'%' . trim($_REQUEST['order_sn']) . '%\'';
        $filter['order_sn'] = $_REQUEST['order_sn'];
    }
    if (isset($_GET['auid']))
    {
        $sqladd = ' AND a.user_id=' . $_GET['auid'];
    }
   
    $sql = "SELECT COUNT(DISTINCT o.parent_id)  FROM " . $GLOBALS['ecs']->table('order_info') . " o".
           " WHERE  o.user_id > 0 AND o.parent_id > 0 $sqladd GROUP BY o.user_id, o.parent_id";
    $count_arr = $GLOBALS['db']->getCol($sql);
	foreach($count_arr as $count)
	{
	  $filter['record_count'] += $count;
	}
    $logdb = array();
    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT DISTINCT o.parent_id,o.order_sn,o.order_id,o.add_time,u.reg_time,o.order_status,o.parent_id,o.is_separate, a.log_id,".
	       "u.user_name as auser,up.user_name as puser, a.money, a.point, a.separate_type,u.parent_id as up,a.user_id as suid FROM " 
		   . $GLOBALS['ecs']->table('order_info') . " o".
                    " LEFT JOIN".$GLOBALS['ecs']->table('users')." u ON o.user_id = u.user_id".
					" LEFT JOIN".$GLOBALS['ecs']->table('users')." up ON o.parent_id = up.user_id".
                    " LEFT JOIN " . $GLOBALS['ecs']->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND o.parent_id > 0  $sqladd" .
                    " GROUP BY o.user_id, o.parent_id ORDER BY o.order_id ASC" .
                    " LIMIT " . $filter['start'] . ",$filter[page_size]";
    $query = $GLOBALS['db']->query($sql);
    while ($rt = $GLOBALS['db']->fetch_array($query))
    {
        if(empty($separate_by) && $rt['up'] > 0)
        {
            //按推荐注册分成
            $rt['separate_able'] = 1;
        }
        elseif(!empty($separate_by) && $rt['parent_id'] > 0)
        {
            //按推荐订单分成
            $rt['separate_able'] = 1;
        }
        if(!empty($rt['suid']))
        {
            //在affiliate_log有记录
            $rt['info'] = sprintf($GLOBALS['_LANG']['separate_info2'], $rt['suid'], $rt['auser'], $rt['money'], $rt['point']);
            if($rt['separate_type'] == -1 || $rt['separate_type'] == -2)
            {
                //已被撤销
                $rt['is_separate'] = 3;
                $rt['info'] = "<s>" . $rt['info'] . "</s>";
            }
        }
        $rt['formated_add_time'] = local_date('Y-m-d H:i', $rt['add_time']);
	    $rt['formated_reg_time'] = local_date('Y-m-d H:i', $rt['reg_time']);
        $logdb[] = $rt;
    }
    $arr = array('logdb' => $logdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

function write_affiliate_log($oid, $uid, $username, $money, $point, $separate_by)
{
    $time = gmtime();
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('affiliate_log') . "( order_id, user_id, user_name, time, money, point, separate_type)".
                                                              " VALUES ( '$oid', '$uid', '$username', '$time', '$money', '$point', $separate_by)";
    if ($oid)
    {
        $GLOBALS['db']->query($sql);
    }
}
?>