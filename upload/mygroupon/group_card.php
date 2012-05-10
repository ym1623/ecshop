<?php

/**
 * ECGROUPON 团购卷的处理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: bonus.php 17063 2010-03-25 06:35:46Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
if ($_REQUEST['act'] == 'list')
{
	 admin_priv('view_card');
		
    $smarty->assign('full_page',    1);
    $smarty->assign('ur_here',      $_LANG['bonus_list']);
    $smarty->assign('action_link',   array('href' => 'group_card.php?act=list', 'text' => $_LANG['group_list']));

    $list = get_group_cart_list();
    $smarty->assign('status_list',  $_LANG['card_status']); 
    $smarty->assign('group_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('group_card_list.htm');
}
elseif ($_REQUEST['act'] == 'query_card')
{ 
    //check_authz_json('view_card');

    $list = get_group_cart_list();

    $smarty->assign('group_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('group_card_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
elseif ($_REQUEST['act'] == 'remove_card')
{
    check_authz_json('remove_card');

    $id = intval($_GET['id']);

    $db->query("DELETE FROM " .$ecs->table('group_card'). " WHERE card_id='$id'");

    $url = 'group_card.php?act=query_card&' . str_replace('act=remove_card', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
elseif ($_REQUEST['act'] == 'send_sms')
{       
        check_authz_json('send_card');
        $card_id = trim($_GET['card_id']);
		$sql = "SELECT card_password,card_sn,group_id,order_sn,user_id,end_date,is_saled,send_num FROM " . $ecs->table('group_card').
		       " WHERE card_id='$card_id' AND is_used='0'";	   
		$card_arr = $db->getRow($sql);
		if ($GLOBALS['_CFG']['send_sms_num'] > 0 && $card_arr['send_num'] >= $GLOBALS['_CFG']['send_sms_num'])
		{
		     $msg = '您的短信发送次数已达到最高限制:'.$GLOBALS['_CFG']['send_sms_num'] . ',请不要频繁发送短信!';
		     make_json_error($msg);
		}
		if (!empty($card_arr))
		{
		   $order_sn = $card_arr['order_sn'];	
		   $sql = "SELECT mobile FROM " . $ecs->table('order_info') . 
		          " WHERE order_sn='$order_sn'";
		  $mobile = $db->getOne($sql);
		
		  $group_id = $card_arr['group_id'];
		  $sql = "SELECT goods_name FROM " . $ecs->table('group_activity') . " WHERE group_id='$group_id'";
		  $goods_name = $db->getOne($sql);
          include_once(ROOT_PATH.'includes/cls_sms.php');
          $sms = new sms();
		  $tpl = get_sms_template('send_sms');
		  $GLOBALS['smarty']->assign('group_name', $goods_name);
		  $GLOBALS['smarty']->assign('card_sn', $card_arr['card_sn']);
		  $GLOBALS['smarty']->assign('card_password', $card_arr['card_password']);
		  $GLOBALS['smarty']->assign('past_time',  local_date('Y-m-d', $card_arr['end_date']));
		  $msg = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);		 
		  if ($mobile != '')
		  {
			 if ($sms->send($mobile, $msg, 0))
		     {      
			        $update = $card_arr['is_saled'] == '0' ? ',is_saled=1' : '';
		            $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET send_num=send_num+1 $update WHERE card_id='$card_id'";
					$db->query($sql);
		     }
		  }
		
	    }
    $url = 'group_card.php?act=query_card&' . str_replace('act=send_sms', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;

}
elseif ($_REQUEST['act'] == 'batch')
{
    /* 检查权限 */
    admin_priv('remove_card');
    if (isset($_POST['checkboxes']))
    {
        $card_id_list = $_POST['checkboxes'];

       if (isset($_POST['drop']))
        {
            $sql = "DELETE FROM " . $ecs->table('group_card'). " WHERE card_id " . db_create_in($card_id_list);
            $db->query($sql);

           clear_cache_files();

            $link[] = array('text' => $_LANG['group_list'],
                'href' => 'group_card.php?act=list');
            sys_msg(sprintf($_LANG['batch_drop_success'], count($card_id_list)), 0, $link);
        }

    }
    else
    {
        sys_msg($_LANG['no_select_card'], 1);
    }
}



function get_group_cart_list()
{
    /* 查询条件 */
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'card_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['group_id'] = empty($_REQUEST['group_id']) ? 0 : intval($_REQUEST['group_id']);
    $filter['group_name'] = empty($_REQUEST['group_name']) ? '' : trim($_REQUEST['group_name']);
    $filter['card_sn'] = empty($_REQUEST['card_sn']) ? '' : trim($_REQUEST['card_sn']);
	$filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
    $filter['group_id'] = empty($_REQUEST['group_id']) ? 0 : trim($_REQUEST['group_id']);
	$filter['status'] = empty($_REQUEST['status']) ? 0 : intval($_REQUEST['status']);
	$where = ' WHERE 1';
	if ($filter['group_name'] != '')
	{
		$sql = "SELECT group_id FROM " . $GLOBALS['ecs']->table('group_activity') . 
		       " WHERE group_name LIKE '%" . mysql_like_quote($filter['group_name']) . "%'";
		$group_id = $GLOBALS['db']->getCol($sql);
		if (!empty($group_id))
		{
		  $group_id = implode(',',$group_id);
		  $where .= " AND gc.group_id in(" . $group_id .")";
		}  
	}
    if ($filter['group_id'] != '')
	{
		$where .= " AND gc.group_id='$filter[group_id]'";
	}
	if ($filter['card_sn'] != '')
	{
		$where .= " AND gc.card_sn LIKE '%" . mysql_like_quote($filter['card_sn']) . "%'";
	}
	if ($filter['order_sn'] != '')
	{
		$where .= " AND gc.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
	}

    $now = gmtime();
    switch($filter['status'])
    {
         case 1:
		    $where .= " AND gc.is_used='0'";
		    break;
		 case 2:
		    $where .= " AND gc.is_used='1'";
		    break;
		 case 3:
		    $where .= " AND end_date <= '$now'";
		    break;
		 case 4:
		    $where .= " AND end_date > '$now'";
		    break;			      
    }

    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('group_card') . " AS gc" . $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
   
    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT gc.*,u.user_name,ga.group_name,ga.goods_name FROM ".$GLOBALS['ecs']->table('group_card'). " AS gc ".
          " LEFT JOIN " .$GLOBALS['ecs']->table('users'). " AS u ON u.user_id=gc.user_id ".
          " LEFT JOIN " .$GLOBALS['ecs']->table('group_activity'). " AS ga ON ga.group_id=gc.group_id $where ".
          " GROUP BY gc.group_id,gc.card_id ORDER BY ".$filter['sort_by']." ".$filter['sort_order'];
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);
    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['use_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['use_date']);
		$row['end_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['end_date']);
		$row['add_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['add_date']);
		$arr[] = $row;
	}

    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>