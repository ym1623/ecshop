<?php

/**
 * ECSHOP 会员中心
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: user.php 17067 2010-03-26 03:59:37Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

$user_id = $_SESSION['user_id'];
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
assign_public($city_id);
$gid = intval($_GET['gid']);
if ($user_id > 0)
{
   $size = 16;
   $page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
   $cache_id = $_CFG['lang'] . '-' . $size . '-' . $page . '-' . $city_id . '-' . $cat_id;
$cache_id = sprintf('%X', crc32($cache_id));

    /* 如果没有缓存，生成缓存 */

	$sql = 'SELECT goods_rebate FROM '. $GLOBALS['ecs']->table('group_activity') . " WHERE group_id='$gid'"; 
    $goods_rebate =  $GLOBALS['db']->getOne($sql);
    if (empty($goods_rebate))
    {
  	  $goods_rebate = 0;
    }
	$count = get_invite_count($user_id);
    if ($count > 0)
    {
       $invite_user = get_invite_user($user_id,$size, $page);
       $smarty->assign('invite_user',  $invite_user);
       /* 设置分页链接 */
       $pager = get_group_pager('invite.php', array(), $count, $page, $size);
       $smarty->assign('pager', $pager);
    }
	$config = unserialize($GLOBALS['_CFG']['affiliate']);
     if ($config['on'] == 1)
     {
        if(!empty($config['config']['expire']))
        {
            if($config['config']['expire_unit'] == 'hour')
            {
                $c = 1;
            }
            elseif($config['config']['expire_unit'] == 'day')
            {
                $c = 24;
            }
            elseif($config['config']['expire_unit'] == 'week')
            {
                $c = 24 * 7;
            }
            else
            {
                $c = 1;
            }
			$group_hours = $config['config']['expire'] * $c;
			$smarty->assign('group_hours', $group_hours);
		}
	 }		
     $smarty->assign('goods_rebate', $goods_rebate);
	 $smarty->assign('action', 'order');
	 $smarty->assign('uid', $user_id);
	 $smarty->assign('is_check_rebate',  $GLOBALS['_CFG']['group_rebate']);
	 $indexurl = array('team.php','index.php');
	 $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
	 $smarty->assign('intvite_url',$ecs->url() . rewrite_groupurl($url,array('u' => $user_id),true));
    $smarty->display('group_invite.dwt');

}
else
{
  if ($gid > 0)
  {	
    $sql = 'SELECT goods_rebate FROM '. $GLOBALS['ecs']->table('group_activity') . " WHERE group_id='$gid'"; 
    $goods_rebate =  $GLOBALS['db']->getOne($sql);
    if (empty($goods_rebate))
    {
    	$goods_rebate = 0;
    }
    $smarty->assign('goods_rebate', $goods_rebate);
   }
  $smarty->assign('uid', '0');	
  $smarty->display('group_invite.dwt');	
}

function get_invite_count($user_id)
{
  $user_num = 0;
  if ($user_id > 0)
  {
     $sql = 'SELECT COUNT(*)  FROM '. $GLOBALS['ecs']->table('order_info') . " AS o".
            " WHERE o.parent_id = '$user_id' AND o.extension_code = 'group_buy' AND is_separate=1" .
		    " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
            " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
     $user_num = $GLOBALS['db']->getOne($sql);
  }
  return $user_num;
}
function get_invite_user($user_id, $size, $page)
{
      $sql = 'SELECT o.extension_id,o.add_time,o.user_id,ga.goods_rebate,ga.group_id,ga.group_name FROM '.
           $GLOBALS['ecs']->table('order_info') . " AS o," . $GLOBALS['ecs']->table('group_activity') . " AS ga ".
           " WHERE ga.group_id=o.extension_id AND o.parent_id = '$user_id' AND o.extension_code = 'group_buy' AND o.is_separate=1" .
		    " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
            " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
           
      $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
	  $arr = array();
 	  while ($row = $GLOBALS['db']->fetchRow($res))
	  {
	     $sql = "SELECT user_name FROM " . $GLOBALS['ecs']->table('users') . "WHERE user_id='$row[user_id]'";
		 $user_name = $GLOBALS['db']->getOne($sql);
		 if ($user_name != '')
		 {
	       $row['user_name'] = $user_name;
		   $row['formated_add_time'] = local_date('Y-m-d', $row['add_time']);
		   $row['formated_goods_rebate'] = group_price_format($row['goods_rebate']);
		   $arr[] = $row;
		 } 
	  }    
    return $arr;
}
?>