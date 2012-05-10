<?php

/**
 * ECSHOP 订单批量发货管理程序
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
 */

define('IN_ECS', true);

/* 包含文件 */
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_code.php');

if ($_REQUEST['act'] == 'append_order')
{
    assign_query_info();

    /* 检查权限 */
    admin_priv('importorder');
	

    $smarty->assign('ur_here', '单个订单发货');
    $smarty->assign('action_link', array('text'=>'订单列表', 'href'=>'order.php?act=list'));
    $smarty->display('order_replenish_info.htm');
}
elseif ($_REQUEST['act'] == 'action')
{
    /* 检查权限 */
    admin_priv('importorder');

    $order_sn = trim($_POST['order_sn']);
    $invoice_no = $_POST['invoice_no'];
	$add_time = gmtime();
	if($order_sn != '')
	{
      $sql = "SELECT order_id,order_sn,mobile,extension_id,pay_status,shipping_status FROM " .
		    $ecs->table('order_info') .
		   " WHERE order_sn='$order_sn'";
      $order = $db->getRow($sql);		
      if ($order['order_id'] > 0 && $order['shipping_status'] == 0 && in_array($order['pay_status'],array(PS_PAYED,PS_PAYING)))
	  {
		   $order_id = $order['order_id'];
		   $sql = 'UPDATE ' . $ecs->table('order_info') . 
		       " SET order_status='". OS_CONFIRMED ."',shipping_status='".SS_SHIPPED."',shipping_time='$add_time',".
			   "invoice_no='$invoice_no' WHERE order_id='$order_id'";
		   $db->query($sql);	
		   order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $order['pay_status'], $action_note);
		   $group_id = $order['extension_id'];
		  if ($group_id > 0 && $order['mobile'] != '' && $_POST['is_sendsms'] == 1)
          {
            include_once(ROOT_PATH.'includes/cls_sms.php');
            $sms = new sms();
	        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('group_activity') . 
			        " WHERE group_id = '$group_id'";
	         $goods_name = $GLOBALS['db']->getOne($sql);
	        $invoice_msg = !empty($invoice_no) ? ',订单信息是' . $invoice_no : ''; 
            $msg =   $GLOBALS['_CFG']['group_shopname'] .
			        '温馨提示:您购买的'.$goods_name .'已发货'. $invoice_msg .',请您注意查收。'.
					$GLOBALS['_CFG']['group_shopname'].'欢迎您常回来看看哦!';
            $sms->send($order['mobile'], $msg, 0);
           }
	   }
	}
    $link[] = array('text'=>'发货成功', 'href'=>'inmport_order.php?act=append_order');
    //$link[] = array('text'=>$_LANG['continue_add'], 'href'=>'order.php?act=list');
    sys_msg($_LANG['action_success'], 0, $link);
}

/* 批量上传页面 */
elseif ($_REQUEST['act'] == 'import_order')
{
    /* 检查权限 */
    admin_priv('importorder');

    $smarty->assign('ur_here',          $_LANG['batch_order_add']);
	$smarty->assign('action_link',      array('text'=>'单个发货', 'href'=>'import_order.php?act=append_order'));
    $smarty->display('import_order.htm');
}

elseif ($_REQUEST['act'] == 'batch_confirm')
{
    /* 检查上传是否成功 */
    if ($_FILES['uploadfile']['tmp_name'] == '' || $_FILES['uploadfile']['tmp_name'] == 'none')
    {
        sys_msg($_LANG['uploadfile_fail'], 1);
    }

    $data = file($_FILES['uploadfile']['tmp_name']);
    $rec = array(); //数据数组
    $i = 1;
    $separator = trim($_POST['separator']);
	$shippname = trim($_POST['shippname']);
    foreach ($data as $line)
    {
        $row = explode($separator, $line);
		if ($row[0] != '')
		{
          $rec[$i]['order_sn']  = $row[0];
          $rec[$i]['invoice_no'] =  $invoice_no = ecs_iconv('GB2312', 'UTF8',$row[1]);;
          $i++;
		}
    }
   // print_r($rec);
    $smarty->assign('ur_here',          $_LANG['batch_order_add']);
    $smarty->assign('action_link',      array('text'=>$_LANG['batch_order_add'], 'href'=>'import_order.php?act=import_order'));
    $smarty->assign('list',               $rec);
	$smarty->assign('shippname',         $shippname);
    $smarty->display('group_order_confirm.htm');

}
/* 批量上传处理 */
elseif ($_REQUEST['act'] == 'batch_insert')
{
    /* 检查权限 */
    admin_priv('importorder');

    $add_time = gmtime();
    $i = 0;
	$shippname = trim($_POST['shippname']);
   if ($_POST['checked'])
   {
    foreach ($_POST['checked'] as $key)
    {
        $order_sn  = trim($_POST['order_sn'][$key]);
        $invoice_no = $shippname . $_POST['invoice_no'][$key];
		$sql = "SELECT order_id,order_sn,mobile,extension_id,pay_status,shipping_status FROM " .
		        $ecs->table('order_info') .
				" WHERE order_sn='$order_sn'";
		 $order = $db->getRow($sql);		
       if ($order['order_id'] > 0 && $order['shipping_status'] == 0 && in_array($order['pay_status'],array(PS_PAYED,PS_PAYING)))
	   {
		   $order_id = $order['order_id'];
		   $sql = 'UPDATE ' . $ecs->table('order_info') . 
		       " SET order_status='". OS_CONFIRMED ."',shipping_status='".SS_SHIPPED."',shipping_time='$add_time',".
			   "invoice_no='$invoice_no' WHERE order_id='$order_id'";
		   $db->query($sql);	
		   order_action($order_sn, OS_CONFIRMED, SS_SHIPPED, $order['pay_status'], $action_note);
		   $group_id = $order['extension_id'];
		  if ($group_id > 0 && $order['mobile'] != '' && $_POST['is_sendsms'] == 1)
          {
            include_once(ROOT_PATH.'includes/cls_sms.php');
            $sms = new sms();
	        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('group_activity') . 
			        " WHERE group_id = '$group_id'";
	         $goods_name = $GLOBALS['db']->getOne($sql);
             $invoice_msg = !empty($invoice_no) ? ',订单信息是' . $invoice_no : ''; 
			$msg =   $GLOBALS['_CFG']['group_shopname'] .
			        '温馨提示:您购买的'.$goods_name .'已发货'. $invoice_msg .',请您注意查收。'.
					$GLOBALS['_CFG']['group_shopname'].'欢迎您常回来看看哦!';
            $sms->send($order['mobile'], $msg, 0);
          }
         $i++;
		}
      } 
   }
    $link[] = array('text' => $_LANG['order'] , 'href' => 'order.php?act=list');
    sys_msg(sprintf($_LANG['batch_order_add_ok'], $i) , 0, $link);
}

?>