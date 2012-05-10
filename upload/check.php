<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];assign_public($city_id);
$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);
if ($id <= 0 || $user_id <= 0)
{
   	$indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
    ecs_header("Location: $url\n");
    exit;
}
      include_once(ROOT_PATH . 'includes/lib_order.php');

       /* 订单详情 */
      $sql = "SELECT order_id,pay_id,goods_amount,shipping_fee,goods_amount+shipping_fee AS all_amount,bonus," .
	         "extension_id,order_amount,surplus,pay_status,order_status FROM " . $ecs->table('order_info') .
			 " WHERE order_id='$id' AND user_id='$user_id'";
	  $orders = $db->getRow($sql);
	  if ($orders['order_status'] == OS_CANCELED || $orders['order_status'] == OS_INVALID || $orders['order_status'] == OS_RETURNED)
	  {
		   $msg = array(OS_CANCELED => '已取消',OS_INVALID => '无效' , OS_RETURNED => '已退货'); 
	       show_group_message('此订单' . $msg[$orders['order_status']] . ',您不能付款了!', '', "myorders.php", 'error');
		   exit; 
	  }
	  if ($orders['pay_status'] == PS_PAYING || $orders['pay_status'] == PS_PAYED)
	  {
	       show_group_message('此订单已付款!', '', "myorders.php", 'error');
		   exit; 
	  }
	  if ($orders['extension_id'] > 0)
	  { 
	    $now = gmtime();
	    $sql = "SELECT group_id,is_hdfk,activity_type FROM " . $GLOBALS['ecs']->table('group_activity') 
           ." WHERE start_time <= '$now' AND end_time >= '$now' AND is_finished ='0' AND group_id='$orders[extension_id]'";
		$group_goods = $db->getRow($sql);   
	    if ($group_goods['group_id'] != $orders['extension_id'])
	    {
	       show_group_message('团购已结束,您不能进行付款了!', '', "myorders.php", 'error');
           exit; 
	    }
	  }
	  else
	  {
	      $indexurl = array('team.php','index.php');
	      $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
		  show_group_message('非法入口!', '', "$url", 'error');
          exit; 
      }
	  $sql = "SELECT rec_id, goods_id, goods_name, goods_sn, market_price, goods_number, " .
            "goods_price, goods_attr, is_real, parent_id, is_gift, " .
            "goods_price * goods_number AS subtotal, extension_code " .
            "FROM " . $GLOBALS['ecs']->table('order_goods') .
            " WHERE order_id = '$id'";
       $group_arr = $GLOBALS['db']->getRow($sql);
	   
	  $user_money = $db->getOne("SELECT user_money FROM " . $ecs->table('users') . " WHERE user_id='$user_id'");
      $user_money = $user_money > 0 ? $user_money : '0';
      $used_money = min($orders['order_amount'], $user_money); 
      $orders['order_amount'] = $orders['order_amount'] - $used_money;
      $group_arr['user_money'] = $user_money;
  	  $group_arr['used_money'] = $orders['surplus'] + $used_money;
	  $group_arr['bonus'] = $orders['bonus'];
	  $group_arr['is_use_bonus'] = $orders['bonus'] > 0 ? '1' : '0';
	  $group_arr['all_amount'] = $orders['all_amount'];
	  $group_arr['pay_money'] = $orders['order_amount'];  
	  $group_arr['shipping_fee'] = $orders['shipping_fee'];	
	  $group_arr['formated_market_price'] = group_price_format($group_arr['market_price']);
      $group_arr['formated_goods_price']  = group_price_format($group_arr['goods_price']);
      $group_arr['formated_subtotal']     = group_price_format($group_arr['subtotal']);
	  $group_arr['formated_user_money'] = group_price_format($group_arr['user_money']);
	  $group_arr['formated_used_money'] = group_price_format($group_arr['used_money']);
	  $group_arr['formated_shipping_fee'] = group_price_format($orders['shipping_fee']);
	  $group_arr['formated_pay_money'] = group_price_format($orders['order_amount']);
	  $group_arr['formated_all_amount'] = group_price_format($orders['all_amount']);
	  $group_arr['formated_bonus'] = group_price_format($orders['bonus']);
	  $group_arr['pay_id'] = $orders['pay_id'];
      $smarty->assign('group_arr',   $group_arr);
	  $smarty->assign('order_id',   $orders['order_id']);
      $is_hdfk = false; 
      if ($group_arr['is_real'] == 2)
      {
         $is_hdfk = $group_goods['is_hdfk'] == 1 ? true : false;
      }
	  if ($group_goods['activity_type'] == 2)
	{
	    if ($_CFG['group_secondspay'] == 1)
	    {
	      $payment_list = group_payment_list(true,$is_hdfk);
	      $smarty->assign('payment_list', $payment_list);
	      $smarty->assign('group_secondspay', $_CFG['group_secondspay']);
	    }
	    else
	    {
	      $smarty->assign('group_secondspay', '0');
	    }
      }
	  else
	  {
	   	$payment_list = group_payment_list(true,$is_hdfk);
	    $smarty->assign('payment_list', $payment_list);
	    $smarty->assign('group_secondspay', '0');
	  }
	  $smarty->assign('act', 'update');
      $smarty->assign('payment_list', $payment_list);
	$smarty->display('group_order.dwt');
	
	
?>