<?php

/**
 * ECGROUPON 团购流程文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/buy.php');

$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

if (empty($_GET['a']))
{
	$_GET['a'] = 'cart';
}
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];

if ($_GET['a'] == 'update')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
    $result = array('error' => 0, 'total' => '', 'num' => '', 'rid' => '','group_price'=>'');
    $json  = new JSON;
    $num = intval($_POST['number']) > 0 ? intval($_POST['number']) : '1';
	$rid = intval($_POST['rid']);
	$group_id = intval($_POST['group_id']);
	$group_attr_id = isset($_POST['group_attr']) ? trim($_POST['group_attr']) : '';
    $group_attr = '';
    $group_buy = get_group_insert($group_id, $num);
	if ($group_attr_id != '')
	{
      $group_attr = get_group_attr_info($group_attr_id);
	  $spec_price = group_spec_price($group_attr_id);
	  $group_buy['group_price'] = $group_buy['group_price'] + $spec_price;
    }
	$cart = array(
	    'goods_attr'    => addslashes($group_attr),
        'goods_attr_id' => $group_attr_id,
        'goods_price'    => $group_buy['group_price'],
        'goods_number'   => $group_buy['number']
    );
	$sql = "session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GROUP_BUY_GOODS . "'" . " AND rec_id='$rid'";
    
	$db->autoExecute($ecs->table('cart'), $cart, 'UPDATE', $sql);
	$sql = "SELECT goods_price*goods_number AS group_total,goods_number,is_real FROM " .
	       $ecs->table('cart') . " WHERE session_id = '" . SESS_ID .
	       "' AND rec_type = '" . CART_GROUP_BUY_GOODS . "'";
	$group_cart = $db->getRow($sql);
	if ($group_cart['is_real'] == 2)
	{
	   $goods_arr['goods_num'] = $group_cart['goods_number'];
	   $goods_arr['goods_amount'] = $group_cart['group_total'];
	   $region = array();
	    if ($_SESSION['user_id'] > 0)
       {
         $consignee = get_group_consignee($_SESSION['user_id']);
	     $region['country'] = $consignee['country'];
	     $region['province'] = $consignee['province'];
	     $region['city'] = $consignee['city'];
	     $region['district'] = $consignee['district'];
       }
	   if (isset($_POST['country']) && isset($_POST['province']) && isset($_POST['city']) && isset($_POST['district']))
	   {
	     $region['country']  = intval($_POST['country']);
         $region['province'] = intval($_POST['province']);
         $region['city']     = intval($_POST['city']);
         $region['district'] = intval($_POST['district']);
	   }
	   $shipping_arr = get_shipping_free($group_buy['group_id'],$goods_arr,$region);
	   $shipping_fee = $shipping_arr['shipping_fee'];
	}
	else
	{
	  $shipping_fee = 0;	
	}
	$result['rid'] = $rid;
	$result['num'] = $group_buy['number'];
    $result['shipping_fee'] = $shipping_fee;
	$result['limitnum'] = $group_buy['limitnum'];
	$result['total_cart'] = $group_cart['group_total'];
	$result['total_order'] = $group_cart['group_total'] + $shipping_fee;
	$result['formated_total_order'] = group_price_format($group_cart['group_total'] + $shipping_fee);
	$result['formated_shipping_fee'] = group_price_format($shipping_fee); 
	$result['formated_total_cart'] = group_price_format($group_cart['group_total']);
	$result['formated_group_price'] = group_price_format($group_buy['group_price']);
	die($json->encode($result));
}

elseif ($_POST['a'] == 'buy')
{
    /* 查询：取得参数：团购活动id */
    $group_buy_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
    if ($group_buy_id <= 0)
    {
       	$indexurl = array('team.php','index.php');
	   $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
       ecs_header("Location: $url\n");
       exit;
    }

    /* 查询：取得数量 */
    $number = isset($_POST['number']) ? intval($_POST['number']) : 1;
    $number = $number < 1 ? 1 : $number;
    /* 查询：取得团购活动信息 */
	
	$group_buy = get_group_insert($group_buy_id, $number);
    if (empty($group_buy) || $group_buy['is_finished'] > 0)
    {   
        $indexurl = array('team.php','index.php');
	    $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
        ecs_header("Location: $url\n");
        exit;
    }
    if ($_SESSION['user_id'] > 0 && !$group_buy['is_limit'])
	{
		$ordernum = get_success_num($group_buy['group_id'], $_SESSION['user_id']);
		if ($ordernum >= 1)
		{
		  assign_public($city_id);
		  show_group_message('此次团购只能购买一次!', '', '', 'info');
		}
	}
    /* 更新：加入购物车 */
    $cart = array(
        'user_id'        => $_SESSION['user_id'],
        'session_id'     => SESS_ID,
        'goods_id'       => $group_buy['group_id'],
        'goods_name'     => addslashes($group_buy['group_name']),
        'market_price'   => $group_buy['market_price'],
        'goods_price'    => $group_buy['group_price'],
        'goods_number'   => $group_buy['number'],
        'is_real'        => $group_buy['goods_type'],
        'parent_id'      => 0,
        'rec_type'       => CART_GROUP_BUY_GOODS,
		'extension_code' => 'group_buy',
        'is_gift'        => 0,
		'is_shipping'    => $group_buy['is_shipping']
    );
	$type = CART_GROUP_BUY_GOODS;
	$_SESSION['goods_type'] = $group_buy['goods_type'];
	$sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' AND rec_type = '$type'";
    $GLOBALS['db']->query($sql);
    $db->autoExecute($ecs->table('cart'), $cart, 'INSERT');
    ecs_header("Location: ./buy.php?a=cart\n");
    exit;
}
elseif ($_REQUEST['a'] == 'address')
{
		$consignee = array();
		if (!empty($_POST['address_id']))
		{
		  $consignee['address_id'] = intval($_POST['address_id']);
		}
		if (!empty($_POST['country']))
		{
		  $consignee['country'] = intval($_POST['country']);
		}
		if (!empty($_POST['province']))
		{
		  $consignee['province'] = intval($_POST['province']);
		}
		if (!empty($_POST['city']))
		{
		  $consignee['city'] = intval($_POST['city']);
		}
		if (!empty($_POST['district']))
		{
		  $consignee['district'] = intval($_POST['district']);
		}
		if (!empty($_POST['consignee']))
		{
		  $consignee['consignee'] = trim($_POST['consignee']);
		}
		if (!empty($_POST['address']))
		{
		  $consignee['address'] = trim($_POST['address']);
		}
		if (!empty($_POST['zipcode']))
		{
		  $consignee['zipcode'] = make_semiangle(trim($_POST['zipcode']));
		}
		if (!empty($_POST['mobile']))
		{
		  $consignee['mobile'] = make_semiangle(trim($_POST['mobile']));
		}
		if ($_SESSION['goods_type'] == 2)
		{
		  if (empty($consignee['consignee']) || empty($consignee['address']) || empty($consignee['mobile']))
		  {
			assign_public($city_id);  
		    show_group_message('请填写完整收货地址!', '', '/buy.php', 'info');
		  }
		}
		else
		{
		  if (empty($consignee['mobile']))
		  {
		   assign_public($city_id);  
		   show_group_message('请填写您的手机号,以得到团购券!', '', '/buy.php', 'info');
		  }
		}
        if ($_SESSION['user_id'] > 0)
        {
            include_once(ROOT_PATH . 'includes/lib_transaction.php');

            /* 如果用户已经登录，则保存收货人信息 */
            $consignee['user_id'] = $_SESSION['user_id'];

            save_consignee(&$consignee, true);
        }

        /* 保存到session */
        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
        $_SESSION['postscript'] = trim($_POST['postscript']);
	    ecs_header("Location: buy.php?a=order\n");
        exit;
}
elseif ($_GET['a'] == 'order')
{
	if ($_SESSION['user_id'] <= 0)
    {
        ecs_header("Location: buy.php?a=login\n");
        exit;
    }
    
	$group_goods = get_group_goods(); // 取得商品列表，计算合计

    if (count($group_goods) == 0)
    {
         $indexurl = array('team.php','index.php');
	     $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
         ecs_header("Location: $url\n");
         exit;
    }
    require(ROOT_PATH . 'includes/lib_order.php');

    /* 对商品信息赋值 */
    $group_id = $group_goods['goods_id'];
    $sql = "SELECT group_id,is_hdfk,is_limit,is_finished,activity_type FROM " . 
	        $ecs->table('group_activity') . " WHERE group_id = '$group_id'";
    $group_buy = $db->getRow($sql);
	if ($group_buy['is_finished'] > 0)
	{
	   $indexurl = array('team.php','index.php');
	   $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
       ecs_header("Location: $url\n");
       exit;
	}
	assign_public($city_id);
    if (!$group_buy['is_limit'])
	{
		$ordernum = get_success_num($group_buy['group_id'], $_SESSION['user_id']);
		if ($ordernum >= 1)
		{
		  clear_cart(CART_GROUP_BUY_GOODS);		
		  show_group_message('此商品只能购买一次!', '', '', 'info');
		}
	}
    
	if (!$group_goods['is_fee_shipping'])
	 {
	   $goods_arr['goods_num'] = $group_goods['goods_number'];
	   $goods_arr['goods_amount'] = $group_goods['all_goods_price'];
	   $region = array();
       $consignee = get_group_consignee($_SESSION['user_id']);
	   $region['country'] = $consignee['country'];
	   $region['province'] = $consignee['province'];
	   $region['city'] = $consignee['city'];
	   $region['district'] = $consignee['district'];
	   $shipping_arr = get_shipping_free($group_id,$goods_arr,$region);
	   $shipping_fee = $shipping_arr['shipping_fee'];
	 }
	 else
	 {
	 	$shipping_fee = 0;
	 }
	$group_goods['all_amount']=$group_goods['goods_amount']+$shipping_fee;
	$group_goods['formated_all_amount']=group_price_format($group_goods['goods_amount']+$shipping_fee);

	$sql = "SELECT user_money FROM " . $GLOBALS['ecs']->table('users') .
            " WHERE user_id = '$_SESSION[user_id]'";
    $user_money = $db->getOne($sql);
	
	if ($user_money <= 0)
	{
		$user_money = 0;
		$used_money = 0;
	}
	if ($user_money >= $group_goods['all_amount'])
	{
		$pay_money = '0';
		$used_money = $group_goods['all_amount'];
	}
	else
	{
		$pay_money = $group_goods['all_amount'] - $user_money;
		$used_money = $user_money;
	}
    $is_hdfk = $group_buy['is_hdfk'] == 1 ? true : false;
	$is_hdfk = $group_goods['is_real'] == 2 ? $is_hdfk : false;
	if ($group_buy['activity_type'] == 2)
	{
	  if ($_CFG['group_secondspay'] == 1)
	  {
	    $payment_list = group_payment_list(true,$is_hdfk);
	    $last_payment = last_shipping_and_payment();
	    $smarty->assign('payment_list', $payment_list);
	    $smarty->assign('group_secondspay', $_CFG['group_secondspay']);
        $group_goods['pay_id'] = $last_payment['pay_id'];
	  }
	  else
	  {
	     $smarty->assign('group_secondspay', '0');
	  }
    }
	else
	{
	   	$payment_list = group_payment_list(true,$is_hdfk);
	    $last_payment = last_shipping_and_payment();
	    $smarty->assign('payment_list', $payment_list);
	   $smarty->assign('group_secondspay', '0');
        $group_goods['pay_id'] = $last_payment['pay_id'];
	}
	$group_goods['bonus'] = '0';
    $group_goods['is_use_bonus'] = '0';
	$group_goods['user_money'] = $user_money;
	$group_goods['pay_money'] = $pay_money;
    $group_goods['shipping_fee'] = $shipping_fee;
	$group_goods['formated_bonus'] = group_price_format($group_goods['bonus']);
    $group_goods['formated_user_money'] = group_price_format($user_money);
	$group_goods['formated_used_money'] = group_price_format($used_money);
	$group_goods['formated_pay_money'] = group_price_format($pay_money);
    $group_goods['formated_shipping_fee'] = group_price_format($shipping_fee);
	$smarty->assign('act', 'insert'); 
    $smarty->assign('group_arr', $group_goods);
	$smarty->display('group_order.dwt');
}
elseif ($_GET['a'] == 'pay')
{
  include_once('includes/lib_clips.php');
  include_once('includes/lib_order.php');
  include_once('includes/lib_payment.php');
  if ($_SESSION['user_id'] <= 0)
  {
        /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
        ecs_header("Location: buy.php?a=login\n");
        exit;
  }
  else
  {
  	 $user_id = $_SESSION['user_id'];
  }
   assign_public($city_id);
   if ($_POST['act'] == 'insert')
  {	
	$cart_goods = get_group_goods();
	
    if (count($cart_goods) == 0)
    {
       	$indexurl = array('team.php','index.php');
	   $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
       ecs_header("Location: $url\n");
       exit;
    }
	$group_id = $cart_goods['goods_id'];
    $sql = "SELECT group_id,is_finished,is_limit FROM " . $ecs->table('group_activity') . " WHERE group_id = '$group_id'";
    $group_buy = $db->getRow($sql);
	if ($group_buy['is_finished'] > 0)
	{
	   $url = rewrite_groupurl('team.php');
       ecs_header("Location: $url\n");
       exit;
	}
    if (!$group_buy['is_limit'])
	{
		$ordernum = get_success_num($group_buy['group_id'], $_SESSION['user_id']);
		if ($ordernum >= 1)
		{
		  show_group_message('此商品只能购买一次!', '', '', 'info');
		}
	}
    $consignee = get_group_consignee($_SESSION['user_id']);

    $order = array(
        'shipping_id'     => intval($_CFG['group_shipping']),
        'pay_id'          => intval($_POST['payment']),
        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,
        'postscript'      => trim($_POST['postscript']),
        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
        'user_id'         => $_SESSION['user_id'],
        'add_time'        => gmtime(),
		'postscript'      => $_SESSION['postscript'],
        'order_status'    => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status'      => PS_UNPAYED,
		'surplus'         => 0,
        'agency_id'       => '0'
        );
     /* 收货人信息 */
    foreach ($consignee as $key => $value)
    {
         $order[$key] = addslashes($value);
    }
    $order['extension_code'] = 'group_buy';
    $order['extension_id'] = $cart_goods['goods_id'];

    /* 订单中的商品 */
    if (!$cart_goods['is_fee_shipping'])
	{
	   $goods_arr['goods_num'] = $cart_goods['goods_number'];
	   $goods_arr['goods_amount'] = $cart_goods['all_goods_price'];
	   $region['country'] = $consignee['country'];
	   $region['province'] = $consignee['province'];
	   $region['city'] = $consignee['city'];
	   $region['district'] = $consignee['district'];
       $shipping_arr = get_shipping_free($cart_goods['goods_id'],$goods_arr,$region);
	   $shipping_fee = $shipping_arr['shipping_fee'];
	}
    else
    {
	 	 $shipping_fee = 0;
    }
	/* 配送方式 */
    if ($order['shipping_id'] > 0)
    {
        $shipping = shipping_info($order['shipping_id']);
        $order['shipping_name'] = addslashes($shipping['shipping_name']);
    }
     /* 支付方式 */
    if ($order['pay_id'] > 0)
    {
        $payment = payment_info($order['pay_id']);
        $order['pay_name'] = addslashes($payment['pay_name']);
    }
	if ($cart_goods['goods_amount'] + $shipping_fee > 0 )
	{
	     $order['bonus']  = 0;
         if (isset($_POST['bonus_sn']))
        {
           $bonus_sn = trim($_POST['bonus_sn']);
           $bonus = bonus_info(0, $bonus_sn);
           if (empty($bonus) || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, CART_GROUP_BUY_GOODS))
          {
			$order['bonus'] = 0;  
			$order['bonus_id'] = 0;
          }
          else
         {
            if ($user_id > 0)
            {
                $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                $db->query($sql);
            }
			$order['bonus']  = $bonus['type_money'];
            $order['bonus_id'] = $bonus['bonus_id'];
         }
       }
	   
      $order['shipping_fee'] = $shipping_fee;
     
      $order['goods_amount'] = $cart_goods['goods_amount'];
      $order['order_amount'] = $order['goods_amount'] + $shipping_fee;
      
	  $bonus_money = min($order['order_amount'], $order['bonus']); 
	  $order['order_amount'] = $order['order_amount'] - $bonus_money;
	  $order['bonus'] = $bonus_money;
	  $user_money = $db->getOne("SELECT user_money FROM " . $ecs->table('users') . " WHERE user_id='$_SESSION[user_id]'");
	  $user_money = $user_money > 0 ? $user_money : '0';
	  $user_money = min($order['order_amount'], $user_money); 
      $order['order_amount'] = $order['order_amount'] - $user_money;
	  $order['surplus'] = $user_money;
    }
	else
	{
	   $order['order_amount'] = $cart_goods['goods_amount'];
	}
    /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
    if ($order['order_amount'] <= 0)
    {
        $order['order_status'] = OS_CONFIRMED;
        $order['confirm_time'] = gmtime();
        $order['pay_status']   = PS_PAYED;
        $order['pay_time']     = gmtime();
        //$order['order_amount'] = 0;
    }
    /* 记录扩展信息 */
    $affiliate = unserialize($_CFG['affiliate']);
    if(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1)
    {
        //推荐订单分成
        $parent_id = get_affiliate();
        if($user_id == $parent_id)
        {
            $parent_id = 0;
        }
    }
    else
    {
        //分成功能关闭
        $parent_id = 0;
    }
    $order['parent_id'] = $parent_id;
 
    /* 插入订单表 */
    $error_no = 0;
    do
    {
        $order['order_sn'] = get_order_sn(); //获取新订单号
        $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

        $error_no = $GLOBALS['db']->errno();

        if ($error_no > 0 && $error_no != 1062)
        {
            die($GLOBALS['db']->errorMsg());
        }
    }
    while ($error_no == 1062); //如果是订单号重复则重新提交数据

    $new_order_id = $db->insert_id();
    $order['order_id'] = $new_order_id;
    /* 插入订单商品 */
    $sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
                "order_id, goods_id, goods_name, goods_sn, goods_number, market_price, ".
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".
            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, ".
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id".
            " FROM " .$ecs->table('cart') .
            " WHERE session_id = '".SESS_ID."' AND rec_type= '" . CART_GROUP_BUY_GOODS ."'";

    $db->query($sql);

    /* 清空购物车 */
    clear_cart(CART_GROUP_BUY_GOODS);
    /* 插入支付日志 */
    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);

    /* 取得支付信息，生成支付代码 */
    if ($order['order_amount'] > 0 && $order['pay_id'] > 0)
    {
        //$payment = payment_info($order['pay_id']);

        include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');

        $pay_obj    = new $payment['pay_code'];

        $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));

        $order['pay_desc'] = $payment['pay_desc'];
		$order['pay_name'] = $payment['pay_name'];
        $smarty->assign('pay_online', $pay_online);
    }
        /* 处理余额、积分、红包 */
    if ($order['user_id'] > 0 && $order['surplus'] > 0)
    {
        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
    }
	if ($order['user_id'] > 0 && $order['order_amount'] < 0)
	{
	   log_account_change($order['user_id'], $order['order_amount'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
	}
    if ($order['bonus_id'] > 0)
    {
        use_bonus($order['bonus_id'], $new_order_id);
    }
	if ($order['order_amount'] == '0')
	{ 
	   set_group_rebate($cart_goods['goods_id'],$order['parent_id'],$order['user_id'],$order['order_id'],$order['order_sn']);
	   set_group_stats($cart_goods['goods_id']);	
	   if (($cart_goods['is_real'] == '1' && !$GLOBALS['_CFG']['make_group_card']) || $cart_goods['is_real'] == '3')
	   {
	    $is_send = !$GLOBALS['_CFG']['send_group_sms'];
		send_group_cards($order['order_id'],$order['order_sn'],$_SESSION['user_id'],$order['mobile'],$is_send);
	   }
	}
     $order['formated_order_amount'] = group_price_format($order['order_amount']);
     $smarty->assign('order', $order);
	 unset($_SESSION['postscript']);
	 unset($_SESSION['goods_type']);
     unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
  }
  else
  {  
      $id = intval($_POST['orderid']);
	  $pay_id = intval($_POST['payment']);
      $sql = "SELECT order_id,order_sn,pay_id,goods_amount, order_amount,bonus,".
	         "surplus,mobile,extension_id,pay_status,order_status,parent_id,user_id FROM " .
	         $ecs->table('order_info') .  " WHERE order_id='$id' AND user_id='$user_id'";
	  $order = $db->getRow($sql);
	  if (empty($order))
	  {
	     $indexurl = array('team.php','index.php');
	     $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
         ecs_header("Location: $url\n");
         exit;
	  }
	  if ($order['order_status'] == OS_CANCELED || $order['order_status'] == OS_INVALID || $order['order_status'] == OS_RETURNED)
	  {
		   $msg = array(OS_CANCELED => '已取消',OS_INVALID => '无效' , OS_RETURNED => '已退货'); 
	       show_group_message('此订单' . $msg[$order['order_status']] . ',您不能付款了!', '', "myorders.php", 'error');
		   exit; 
	  }
	  if ($order['pay_status'] == PS_PAYING || $order['pay_status'] == PS_PAYED)
	  {
	       show_group_message('此订单已付款!', '', "myorders.php", 'error');
		   exit; 
	  }
	  if ($pay_id > 0)
      {
        $payment = payment_info($pay_id);
        $pay_name = addslashes($payment['pay_name']);
      }
	  $user_money = $db->getOne("SELECT user_money FROM " . $ecs->table('users') . " WHERE user_id='$user_id'");
	  $user_money = $user_money > 0 ? $user_money : '0';
	  $user_money = min($order['order_amount'], $user_money); 
      $order['order_amount'] = $order['order_amount'] - $user_money;
	  $update_sql = '';
	  if ($user_money > 0)
	  {
	    $surplus = $order['surplus'] + $user_money;
	    $update_sql = ",surplus=$surplus";
	  }
      if ($order['bonus'] <= 0 && isset($_POST['bonus_sn']))
      {
           $bonus_sn = trim($_POST['bonus_sn']);
           $bonus = bonus_info(0, $bonus_sn);
           if (!empty($bonus) && ($bonus['user_id'] <= 0 || $bonus['user_id'] == $_SESSION['user_id']) && $bonus['order_id'] <= 0 && $order['goods_amount'] >= $bonus['min_goods_amount'])
         { 		
             if ($user_id > 0)
             {
               $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
               $db->query($sql);
             }
			 $bonus_money = $bonus['type_money'];
             $bonus_id = $bonus['bonus_id'];
		     $bonus_money = min($order['order_amount'], $bonus_money); 
	         $order['order_amount'] = $order['order_amount'] - $bonus_money;
			 $update_sql .= ",bonus='$bonus_money',bonus_id='$bonus_id'";
		 }
      }
	  if ($order['order_amount'] <= 0)
      {
        $order['order_status'] = OS_CONFIRMED;
        $order['confirm_time'] = gmtime();
        $order['pay_status']   = PS_PAYED;
        $order['pay_time']     = gmtime();
		$update_sql .= ",order_status='" . OS_CONFIRMED . "',confirm_time='" . gmtime() .
		                "',pay_status='" . PS_PAYED . "',pay_time='" . gmtime() . "'";                  
      }

	  $update_sql .= ",order_amount='$order[order_amount]'";
	  $sql = "UPDATE " . $ecs->table('order_info') . " SET pay_id='$pay_id',pay_name='$pay_name' $update_sql WHERE order_id='$id'";
	  $db->query($sql);
	  $sql = "SELECT log_id FROM " . $ecs->table('pay_log') . " WHERE order_id='$id' LIMIT 1";
	  $order['log_id'] = $db->getOne($sql);
	  if ($bonus_id > 0)
	  {
	    use_bonus($bonus_id, $id);
	  }
	  if ($user_id > 0 && $user_money > 0)
      {
        log_account_change($user_id, $user_money * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
      }
      if ($order['order_amount'] == '0')
	 { 
	   set_group_rebate($order['extension_id'],$order['parent_id'],$order['user_id'],$order['order_id'],$order['order_sn']);
	   set_group_stats($order['extension_id']);
	   $sql = "SELECT is_real FROM " . $ecs->table('order_goods') .
	          " WHERE order_id='$id' AND goods_id='$order[extension_id]'";
	   $is_real	= $db->getOne($sql);
	   if (($is_real == '1' && !$GLOBALS['_CFG']['make_group_card']) || $is_real == '3')
	   {
	    $is_send = !$GLOBALS['_CFG']['send_group_sms'];
		send_group_cards($order['order_id'],$order['order_sn'],$_SESSION['user_id'],$order['mobile'],$is_send);
	   }
	 }
	  if ($order['order_amount'] > 0 && $pay_id > 0)
     {
        include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
        $pay_obj    = new $payment['pay_code'];
        $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
        $order['pay_desc'] = $payment['pay_desc'];
		$order['pay_name'] = $payment['pay_name'];
        $smarty->assign('pay_online', $pay_online);
     }
	 $order['formated_order_amount'] = group_price_format($order['order_amount']);
	 $smarty->assign('order',      $order);
 
    }
	$smarty->display('group_pay.dwt');
}
elseif ($_GET['a'] == 'login')
{
	assign_public($city_id);
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
		$smarty->display('group_login.dwt');
		exit;
    }
    else
    {
        include_once('includes/lib_passport.php');
	   include_once('includes/lib_transaction.php');
        if (!empty($_POST['act']) && $_POST['act'] == 'signin')
        {  
            if ($user->login($_POST['username'], $_POST['password']))
            {   	
                /* 检查购物车中是否有商品 没有商品则跳转到首页 */
                $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . 
				         " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GROUP_BUY_GOODS . "'";
                if ($db->getOne($sql) > 0)
                {
					$consignee = get_group_consignee($_SESSION['user_id']);
					$consignee['user_id'] = $_SESSION['user_id'];
					if (!empty($consignee))
					{
					  $address_id = $db->getOne("SELECT address_id FROM " . $ecs->table('users') . " WHERE user_id='$_SESSION[user_id]'");
					  $consignee['address_id'] = !empty($address_id) ? $address_id : '0'; 	
					  save_consignee($consignee, true);
					  $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
                      ecs_header("Location: buy.php?a=order\n");
					}
					else
					{
					  ecs_header("Location: buy.php?a=cart\n");
					}
                }
                else
                {
                    $indexurl = array('team.php','index.php');
	                $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
                    ecs_header("Location: $url\n");  
                }
                exit;
            }
            else
            {   
               show_group_message('用户名或密码错误!', '重新登录', 'buy.php?a=login', 'error');
            }
        }
        elseif (!empty($_POST['act']) && $_POST['act'] == 'signup')
        {
           
            if (register(trim($_POST['username']), trim($_POST['password_reg']), trim($_POST['useremail'])))
            {
                /* 用户注册成功 */
			   $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . 
				         " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GROUP_BUY_GOODS . "'";
                if ($db->getOne($sql) > 0)
                {
					$consignee = get_group_consignee($_SESSION['user_id']);
					$consignee['user_id'] = $_SESSION['user_id'];
					if (!empty($consignee))
					{
					 save_consignee(&$consignee, true);
					 $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
                     ecs_header("Location: buy.php?a=order\n");
					}
					else
					{
					   ecs_header("Location: buy.php?a=cart\n");
					}
                }
				else
                {
                  	$indexurl = array('team.php','index.php');
	                $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
                    ecs_header("Location: $url\n");
                  
                }
                //ecs_header("Location: buy.php?a=cart\n");
                exit;
            }
            else
            {
                ecs_header("Location: buy.php?a=login\n");
            }
        }
        else
        {
            // TODO: 非法访问的处理
        }
    }

}
elseif ($_GET['a'] == 'check_bonus')
{
	include_once('includes/lib_order.php');
    $bonus_sn = trim($_POST['bonus_sn']);
	$orderid = intval($_POST['orderid']);

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');
    if ($orderid > 0)
	{
	  $sql = "SELECT o.order_id,o.goods_amount,o.shipping_fee,o.bonus,o.order_amount,o.surplus,o.extension_id AS goods_id,g.goods_number FROM " .
	         $ecs->table('order_info') . ' AS o,' . $ecs->table('order_goods') . ' AS g'.
			 " WHERE o.order_id=g.order_id AND o.order_id='$orderid' AND o.user_id='$_SESSION[user_id]'";
	  $cart_goods = $db->getRow($sql);
      $cart_goods['is_fee_shipping'] = $cart_goods['shipping_fee'] == '0' ? '1' : '0'; 
	  $is_use_bonus = $cart_goods['bonus'] > 0 ? false : true;
	}
	else
	{
     $is_use_bonus = true;
     $cart_goods = get_group_goods(); 
    }
	if ($is_use_bonus && is_numeric($bonus_sn))
    {
        $bonus = bonus_info(0, $bonus_sn);
    }
    else
    {
        $bonus = array();
    }
    if (empty($cart_goods) || empty($_SESSION['user_id']))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {

        if (!empty($bonus) && ($bonus['user_id'] <= 0 || $bonus['user_id'] == $_SESSION['user_id'])&& $bonus['order_id'] <= 0)
        {
            
            $now = gmtime();
            if ($now > $bonus['use_end_date'])
            {
				$bonus_money = 0;
                $result['error']=$_LANG['bonus_use_expire'];
            }
			elseif($bonus['min_goods_amount'] > $cart_goods['goods_amount'])
		    {
		       $bonus_money = 0;
			   $result['error']=$_LANG['bonus_use_expire'];
		    }
            else
            {
			   $bonus_money = $bonus['type_money'];
            }
        }
		else
        {
            $result['error'] = $_LANG['invalid_bonus'];
        }

        /* 计算订单的费用 */
        if (!$cart_goods['is_fee_shipping'])
	    {
	       $goods_arr['goods_num'] = $cart_goods['goods_number'];
	       $goods_arr['goods_amount'] = $cart_goods['goods_amount'];
	       $region = array();
		   if ($_SESSION['user_id'] > 0)
		   {
            $consignee = get_group_consignee($_SESSION['user_id']);
	        $region['country'] = $consignee['country'];
	        $region['province'] = $consignee['province'];
	        $region['city'] = $consignee['city'];
	        $region['district'] = $consignee['district'];
		   }
		   $shipping_arr = get_shipping_free($cart_goods['goods_id'],$goods_arr,$region);
	       $shipping_fee = $shipping_arr['shipping_fee'];
	
	    }
	    else
	    {
	 	  $shipping_fee = 0;
	    }
	   $user_money = $db->getOne("SELECT user_money FROM " . $ecs->table('users') . " WHERE user_id='$_SESSION[user_id]'");
       $user_money = $user_money > 0 ? $user_money : '0';
       $cart_goods['shipping_fee'] = $shipping_fee;
	   $cart_goods['goods_amount'] = $cart_goods['goods_amount']+$shipping_fee;
	   $bonus_money = min($cart_goods['goods_amount'],$bonus_money); 
	   $cart_goods['pay_money'] = $cart_goods['goods_amount'] - $bonus_money;
	   $cart_goods['bonus_money'] = $bonus_money;
	   
	   $user_money = min($cart_goods['pay_money'], $user_money); 
       
	   $cart_goods['pay_money'] = $cart_goods['pay_money'] - $user_money;
	   $cart_goods['user_money'] = $user_money;
	   $cart_goods['formated_goods_amount'] =  group_price_format($cart_goods['goods_amount']);
	   $cart_goods['formated_shipping_fee'] = group_price_format($shipping_fee);
	   $cart_goods['formated_user_money'] = group_price_format($user_money);
	   $cart_goods['formated_pay_money'] = group_price_format($cart_goods['pay_money']);
	   $cart_goods['formated_bonus_money'] = group_price_format($cart_goods['bonus_money']);
       $smarty->assign('cart_goods', $cart_goods);
      $result['bonus_money'] = $cart_goods['bonus_money'];
	  $result['formated_bonus_money'] = group_price_format($cart_goods['bonus_money']);
	  $result['content'] = $smarty->fetch('library/group_order_total.lbi');
    }
	$json = new JSON();
    die($json->encode($result));
}
elseif ($_GET['a'] == 'shipping')
{
	include_once('includes/lib_order.php');

    include_once('includes/cls_json.php');

    $result = array('error' => '', 'content' => '');
    $region = array();
    $region['country']  = intval($_POST['country']);
    $region['province'] = intval($_POST['province']);
    $region['city']     = intval($_POST['city']);
    $region['district'] = intval($_POST['district']);
	$cart_goods = get_group_goods(); 
	$shipping_fee = 0;
	$free_money = 0;
	if (!$cart_goods['is_fee_shipping'])
	{
	  $goods_arr['goods_num'] = $cart_goods['goods_number'];
	  $goods_arr['goods_amount'] = $cart_goods['goods_amount'];
      $shipping_arr = get_shipping_free($cart_goods['goods_id'],$goods_arr,$region);
	  $shipping_fee = $shipping_arr['shipping_fee'];
	  $free_money = $shipping_arr['free_money'];
	}
	$result['free_money'] = $free_money;
	$result['shipping_fee'] = $shipping_fee;
	$result['total_order'] = $cart_goods['goods_amount'] + $shipping_fee;
    $result['formated_shipping_fee'] = group_price_format($result['shipping_fee']);
    $result['formated_free_money'] = group_price_format($free_money);
	$result['formated_total_order'] = group_price_format($result['total_order']);
    $json = new JSON();
    die($json->encode($result));

}
else
{    
     include_once('includes/lib_order.php');
	 $cart_goods = get_group_goods();
	 if (empty($cart_goods))
	 {
       $indexurl = array('team.php','index.php');
	   $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
       ecs_header("Location: $url\n");
       exit;
	 }
	assign_public($city_id);
	$consignee = get_group_consignee($_SESSION['user_id']);
	 if (!empty($consignee) && $cart_goods['is_real'] == 2)
	 {
	     $city_list   = get_regions(2, $consignee['province']);
         $district_list = get_regions(3, $consignee['city']);
         $smarty->assign('city_list',     $city_list);
         $smarty->assign('district_list', $district_list);
		 $smarty->assign('show_district', '1');
	  }
     $smarty->assign('consignee',$consignee);
	 $smarty->assign('postscript',$_SESSION['postscript']);
	 $cart_goods['user_money'] = 0;
     if ($_SESSION['user_id'] > 0)
     {
	   $user_money = $db->getOne("SELECT user_money FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id='$_SESSION[user_id]'");
	   $user_money = empty($user_money) ? '0': intval($user_money);
	   $cart_goods['user_money'] = $user_money;
     }
	 $shipping_id = 0;
	 if (!$cart_goods['is_fee_shipping'])
	 {
	   $region = array();
	   $group_id = $cart_goods['goods_id'];
	   $goods_arr['goods_num'] = $cart_goods['goods_number'];
	   $goods_arr['goods_amount'] = $cart_goods['all_goods_price'];
	   $region['country'] = $consignee['country'];
	   $region['province'] = $consignee['province'];
	   $region['city'] = $consignee['city'];
	   $region['district'] = $consignee['district'];
       $shipping_arr = get_shipping_free($group_id,$goods_arr,$region);
	   $shipping_fee = $shipping_arr['shipping_fee'];
	   $shipping_id = $shipping_arr['shipping_id'];
	   $free_money = $shipping_arr['free_money'];
	 }
	 else
	 {
	 	$shipping_fee = 0;
		$free_money = 0;
	 }
	 $cart_goods['free_money'] = $free_money;
	 $cart_goods['formated_free_money'] = group_price_format($free_money);
	 $cart_goods['formated_shipping_fee'] = group_price_format($shipping_fee);
	 $cart_goods['formated_user_money'] = group_price_format($cart_goods['user_money']);
	 $cart_goods['goods_amount'] = $cart_goods['goods_amount']+$shipping_fee;
	 $cart_goods['formated_goods_amount'] = group_price_format($cart_goods['goods_amount']);
	 $smarty->assign('goods_type', $cart_goods['is_real']);
     $smarty->assign('group_arr', $cart_goods);
	 $group_attr = get_group_properties($cart_goods['goods_id'],explode(',',$cart_goods['goods_attr_id']));
	 $attr_num = count($group_attr) > 0 ? count($group_attr) : '0';
	 if ($group_attr)
	 {	 
	   $smarty->assign('group_attr', $group_attr);
	 }
	 if ($cart_goods['is_real'] == 2)
	 {
	   $smarty->assign('country_list',       get_regions());
       $smarty->assign('shop_country',       $_CFG['shop_country']);
       $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
	 }
	 $smarty->assign('attr_num', $attr_num);
	 $smarty->assign('shipping_id', $shipping_id);
     $smarty->display('group_cart.dwt');
}

?>