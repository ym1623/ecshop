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
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
$type = trim($_GET['type']);
$type = in_array($type,array('seconds','invite','expense','today')) ? $type : 'seconds';
$cache_id = $_CFG['lang'] . '-' . $city_id . '-' . $type;
$cache_id = sprintf('%X', crc32($cache_id));
if (!$smarty->is_cached('topman.dwt', $cache_id))
{
  if ($type == 'today')
  {
   $smarty->assign('todaylist', get_topman('expense',true));
   $smarty->assign('invitelist', get_topman('invite',true));
  }
  else
  {
    $smarty->assign('topmanlist', get_topman($type));
  }
  $smarty->assign('type', $type);
  $smarty->assign('where', 'topman');
  assign_public($city_id);
}
$smarty->display('topman.dwt', $cache_id);

function get_topman($type = 'seconds',$is_today = false)
{
   $other_sql = '';
   if ($is_today)
   {
      $day = local_getdate(); 
	  $end_time = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	  $start_time = local_mktime(0, 0, 0, $day['mon'], $day['mday'], $day['year']);
      $other_sql = " AND o.add_time >= '$start_time' AND o.add_time <= '$end_time' ";
   }
   if ($type == 'expense')
   {	
      $sql = 'SELECT u.user_id, u.user_name, SUM(og.goods_number) as goods_number,' .
	       'SUM(og.goods_number*(og.market_price-og.goods_price)) AS save_amount '.
           'FROM ' . $GLOBALS['ecs']->table('users') . ' AS u, ' .
                $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
                $GLOBALS['ecs']->table('order_goods') . ' AS og ' .
           "WHERE og.order_id = o.order_id AND u.user_id = o.user_id AND o.extension_code = 'group_buy' ". $other_sql .
           "AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
           "AND (o.pay_status = '" . PS_PAYED . "' OR o.pay_status = '" . PS_PAYING . "') " .
           'GROUP BY u.user_id ORDER BY goods_number DESC, u.user_id DESC LIMIT ' . $GLOBALS['_CFG']['topman_num'];
	}
	elseif ($type == 'invite')
	{
	   	 $sql = 'SELECT u.user_id, u.user_name, count(o.user_id) as user_num,SUM(og.goods_number*ga.goods_rebate) AS all_rebate FROM '.
		        $GLOBALS['ecs']->table('users') . ' AS u, ' .
                $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
                $GLOBALS['ecs']->table('order_goods') . ' AS og,' .
			    $GLOBALS['ecs']->table('group_activity') . ' AS ga ' .
           "WHERE og.order_id = o.order_id AND u.user_id = o.parent_id AND ga.group_id = og.goods_id AND o.extension_code = 'group_buy' " . $other_sql .
           "AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
           "AND (o.pay_status = '" . PS_PAYED . "' OR o.pay_status = '" . PS_PAYING . "') " .
           'GROUP BY o.parent_id ORDER BY user_num DESC, u.user_id DESC LIMIT ' . $GLOBALS['_CFG']['topman_num'];
	}
	else
	{      $day = local_getdate();
	       $sql = 'SELECT u.user_id, u.user_name, SUM(og.goods_number) as goods_number ' .
           'FROM ' . $GLOBALS['ecs']->table('users') . ' AS u, ' .
                $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
                $GLOBALS['ecs']->table('order_goods') . ' AS og, ' .
				$GLOBALS['ecs']->table('group_activity') . ' AS ga ' .
           "WHERE og.order_id = o.order_id AND u.user_id = o.user_id AND o.extension_code = 'group_buy' " . $other_sql .
		   "AND ga.group_id = og.goods_id AND ga.activity_type = 2 ".
           "AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
           "AND (o.pay_status = '" . PS_PAYED . "' OR o.pay_status = '" . PS_PAYING . "') " .
           'GROUP BY u.user_id ORDER BY goods_number DESC, u.user_id DESC LIMIT ' . $GLOBALS['_CFG']['topman_num'];

	}
    $res = $GLOBALS['db']->query($sql);
	$user_list = array();
	$i = 1;
    while ($user = $GLOBALS['db']->fetchRow($res))
    {
		
	    if ($type == 'seconds')
		{
		   $user_id = $user['user_id'];
       	   $end_time = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	       $start_time = local_mktime(0, 0, 0, $day['mon'], $day['mday'], $day['year']);
		   $sql = 'SELECT SUM(og.goods_number) as goods_number FROM' .
                $GLOBALS['ecs']->table('order_info') . ' AS o, ' .
                $GLOBALS['ecs']->table('order_goods') . ' AS og, ' .
				$GLOBALS['ecs']->table('group_activity') . ' AS ga ' .
                "WHERE og.order_id = o.order_id AND o.user_id = '$user_id' AND o.extension_code = 'group_buy' " .
				 "AND ga.group_id = og.goods_id AND ga.activity_type = 2 ".
				"AND o.add_time >= '$start_time' AND o.add_time <= '$end_time' " .
                "AND (o.order_status = '" . OS_CONFIRMED . "' OR o.order_status >= '" . OS_SPLITED . "') " .
                "AND (o.pay_status = '" . PS_PAYED . "' OR o.pay_status = '" . PS_PAYING . "') ";
           $today_number = $GLOBALS['db']->getOne($sql);
		   $user['today_number'] = intval($today_number);
		 }
		 elseif ($type == 'expense')
		 {
		   	$user['formated_save_amount']= group_price_format($user['save_amount']);
		 }
		 else
		 {
		    $user['formated_all_rebate']= group_price_format($user['all_rebate']);
		 }
		$user_list[$i] = $user;
		$i++;
    }
    return $user_list;
}
?>