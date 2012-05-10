<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/merchant.php');

$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

$action = !empty($_REQUEST['act']) ? trim($_REQUEST['act']) : 'group';
if (!empty($_COOKIE['ECS']['suppliers_id']) && !empty($_COOKIE['ECS']['suppliers_password']))
{
        // 找到了cookie, 验证cookie信息
        $sql = 'SELECT suppliers_id,user_name,password,is_ship,parent_id ' .
                ' FROM ' .$ecs->table('suppliers') .
                " WHERE suppliers_id = '" . intval($_COOKIE['ECS']['suppliers_id']) .
				"' AND password = '" .$_COOKIE['ECS']['suppliers_password']. "'";

        $row = $db->GetRow($sql);

        if (!$row)
        {
            // 没有找到这个记录
           $time = time() - 3600;
           setcookie("ECS[suppliers_id]",  '', $time, '/');
           setcookie("ECS[suppliers_password]", '', $time, '/');
        }
        else
        {
			$time = time() + 3600 * 24 * 15;
            $_SESSION['suppliers_id'] = $row['suppliers_id'];
            $_SESSION['suplliers_user'] = $row['user_name'];
			$_SESSION['is_ship'] = $row['is_ship'];
			$_SESSION['parent_id'] = $row['parent_id'];
			setcookie("ECS[suppliers_id]", $row['suppliers_id'], $time, '/');
            setcookie("ECS[suppliers_password]", $row['password'], $time, '/');
		    $action = ($action == 'login') ? 'group' : $action;
        }
}

if (in_array($action,array('coupons','group','settings','act_settings','logout','set_coupons','order','stats')))
{
  $suppliers_id = $_SESSION['suppliers_id'];
  if ($suppliers_id <= '0')
  {
	ecs_header("Location: merchant.php?act=login \n");
    exit;
  }
  else
  {
	$smarty->assign('is_ship',  $_SESSION['is_ship']);
	if ($_SESSION['parent_id'] == 0)
	{
      $sql = "SELECT suppliers_id,suppliers_name FROM " . $ecs->table('suppliers') . " WHERE parent_id='$suppliers_id'";
	  $smallsuppliers = $db->getAll($sql);
	  $smarty->assign('smallsuppliers', $smallsuppliers); 
	}
  	$smarty->assign('suppliers_id', $suppliers_id); 
  }
}
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];

assign_public($city_id);
if ($action == 'login')
{
   $smarty->assign('action', 'login');
   $smarty->display('group_merchant.dwt');
}
elseif ($action == 'act_login')
{
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
	$back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : 'merchant.php?act=group';
    if (empty($username) || empty($password))
	{
	   show_group_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'merchant.php?act=login', 'error');
	}
	$sql = "SELECT suppliers_id,user_name,password,is_ship,parent_id FROM " . $ecs->table('suppliers') . " WHERE user_name='$username'";
	$suppliers = $db->getRow($sql);
    if (!empty($suppliers) && $suppliers['password'] == md5($password))
    {
	    $_SESSION['suppliers_id'] = $suppliers['suppliers_id'];
		$_SESSION['suplliers_user'] = $suppliers['user_name'];
		$_SESSION['is_ship'] = $row['is_ship'];
		$_SESSION['parent_id'] = $row['parent_id'];
	    $time = time() + 3600 * 24 * 15;
		setcookie("ECS[suppliers_id]", $suppliers['suppliers_id'], $time, '/');
        setcookie("ECS[suppliers_password]", $suppliers['password'], $time, '/');
        ecs_header("Location: $back_act\n");
    }
    else
    {   
        show_group_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'merchant.php?act=login', 'error');
    }

}
elseif ($action == 'set_coupons')
{
	include('includes/cls_json.php');
    $json   = new JSON;
    $result = array('error' => '0', 'result' => '', 'msg' => '');
	$card_sn = trim($_POST['card_sn']);
	$card_password = trim($_POST['card_pass']);
	$now = gmtime();
	$suid = 0;
	if (isset($_POST['suid']))
	{
	  if ($_POST['suid'] > 0)
	  {
	    $suid = intval($_POST['suid']);
	  }
      else
      {
	   $result['msg'] = '请选择分店';
	   die($json->encode($result['msg']));
      }
	}
	if ($card_sn == '' || $card_password == '')
	{
		$result['msg'] = $_LANG['card_sn_empty'];
	    die($json->encode($result['msg']));
	}
	$sql = "SELECT card_id,group_id,card_sn,card_password,is_used,end_date,is_saled FROM " . $ecs->table('group_card').
	       " WHERE card_sn='$card_sn' LIMIT 1";
	$group_card = $db->getRow($sql);
    if ($group_card['group_id'] > 0)
	{	
	  $sql = "SELECT suppliers_id FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE group_id = '$group_card[group_id]' ";	
      $expand_supp = $GLOBALS['db']->getCol($sql);
	  $suid = $_SESSION['parent_id'] > 0 ? $_SESSION['suppliers_id'] : $suid;
	  $suid = $suid > 0 ? $suid : $_SESSION['suppliers_id'];
	    if (!empty($expand_supp) && !in_array($suid,$expand_supp))
	    {
	     $result['msg'] = '此券不能在此分店使用';
		 die($json->encode($result['msg']));
	    }
	}
 	$su_id = $db->getOne("SELECT suppliers_id FROM " . $ecs->table('group_activity') . " WHERE group_id = '$group_card[group_id]'");
	if (empty($group_card))
	{
		$result['msg'] = $_LANG['card_sn_error'];
		die($json->encode($result['msg']));
	}
    if (($_SESSION['parent_id'] == 0 && $su_id != $suppliers_id) || ($_SESSION['parent_id'] > 0 && $su_id != $_SESSION['parent_id']))
	{
        $result['msg'] = $_LANG['card_sn_error'];
		die($json->encode($result['msg']));
	}
    if ($group_card['end_date'] <= $now)
	{
		$result['msg'] = $_LANG['card_pass_end'];
		die($json->encode($result['msg']));
	} 
	if ($group_card['is_used'] == 1)
	{
		$result['msg'] = $_LANG['card_used'];
		die($json->encode($result['msg']));
	}
	if ($group_card['card_password'] != $card_password)
	{
		$result['msg'] = $_LANG['card_pass_error'];
		die($json->encode($result['msg']));
	}
	
	$sql = "UPDATE " . $ecs->table('group_card') . 
	       " SET is_used = 1, use_date = '$now',suppliers_id='$suid' WHERE card_id='$group_card[card_id]' LIMIT 1";
	$db->query($sql);
	$result['msg'] = $_LANG['card_success'];
	die($json->encode($result['msg']));
}
elseif ($action == 'delivery')
{
	include('includes/cls_json.php');
    $json   = new JSON;
    $order_sn = trim($_POST['ordersn']);
    $invoice_no = $_POST['invoice'];
	if ($invoice_no == '')
	{
		$result['msg'] = '请输入快递单号!';
	}
	if ($_SESSION['is_ship'] != 1)
	{
		$result['msg'] = '您的帐号不支持此功能,请联系系统管理员!';
    }
	if($order_sn != '' && $invoice_no != '' && $_SESSION['is_ship'] == 1)
	{
      $sql = "SELECT order_id,order_sn,mobile,extension_id,pay_status FROM " .
		    $ecs->table('order_info') .
		   " WHERE order_sn='$order_sn'";
      $order = $db->getRow($sql);		
      if ($order['order_id'] > 0)
	  {
		  if ($order['shipping_status'] == 0 && in_array($order['pay_status'],array(1,2)))
		  {
		   $sql = 'UPDATE ' . $ecs->table('order_info') . 
		       " SET order_status='5',shipping_status='1',shipping_time='$add_time',".
			   "invoice_no='$invoice_no' WHERE order_sn='$order_sn' AND pay_status=2";
		   $db->query($sql);	
		   order_action($order_sn, 5, 1, $order['pay_status'], $action_note);
		   $group_id = $order['extension_id'];
		   if ($group_id > 0 && $order['mobile'] != '')
           {
            include_once(ROOT_PATH.'includes/cls_sms.php');
            $sms = new sms();
	        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('group_activity') . 
			        " WHERE group_id = '$group_id'";
	         $goods_name = $GLOBALS['db']->getOne($sql);
	        $invoice_msg = !empty($invoice_no) ? ',订单信息是' . $invoice_no : ''; 
            $msg =  $_CFG['group_shopname'] . '温馨提示:您购买的'.$goods_name .'已发货'. $invoice_msg .
			        ',请您注意查收。'.$_CFG['group_shopname'].'欢迎您常回来看看哦!';
            $sms->send($order['mobile'], $msg, 0);
			$result['msg'] = '发货成功!';
           }
		  }
		  else
		  {
			  $result['msg'] = '此订单已经发货!';
		  }
	   }
	   else
	   {
		   $result['msg'] = '数据错误,请联系系统管理员!';
	    }
	}
	
	die($json->encode($result['msg']));
}

elseif($action == 'settings')
{
   $sql = "SELECT * FROM " . $ecs->table('suppliers') . " WHERE suppliers_id='$suppliers_id'";
   $suppliers = $db->getRow($sql);
   $smarty->assign('suppliers', $suppliers);
   $smarty->assign('action', 'settings');
   $smarty->display('group_merchant.dwt');
}
elseif($action == 'act_settings')
{
        $suppliers = array(
                           'suppliers_desc'   => trim($_POST['suppliers_desc']),
						   'website'          => trim($_POST['website']),
						   'address'          => trim($_POST['address']),
						   'phone'            => trim($_POST['phone']),
						   'linkman'          => trim($_POST['linkman']),
						   'open_banks'       => trim($_POST['open_banks']),
						   'banks_user'       => trim($_POST['banks_user']),
						   'banks_account'    => trim($_POST['banks_account']),
                           );
        if (trim($_POST['password']) != '')
		{
		  $suppliers['password'] = md5(trim($_POST['password']));
		}
        /* 保存供货商信息 */
        $db->autoExecute($ecs->table('suppliers'), $suppliers, 'UPDATE', "suppliers_id = '" . $suppliers_id . "'");
       show_group_message($_LANG['edit_suppliers'], $_LANG['view_suppliers'], 'merchant.php?act=settings'); 
}
elseif ($action == 'logout')
{
  	 unset($_SESSION['suppliers_id']);
     unset($_SESSION['suplliers_user']);
	 $time = time() - 3600;
     setcookie("ECS[suppliers_id]",  '', $time, '/');
     setcookie("ECS[suppliers_password]", '', $time, '/');
     ecs_header("Location: merchant.php \n");
}
elseif($action == 'group')
{
   $page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
   $size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
   $othersql = '';
	if ($_SESSION['parent_id'] > 0)
	{	
	  $sql = "SELECT group_id FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE suppliers_id = '$suppliers_id' ";	
      $expand_supp = $GLOBALS['db']->getCol($sql);
	  $othersql = " AND ga.group_id " . db_create_in($expand_supp);
      $suppliers_id = $_SESSION['parent_id'];
	}

    $count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('group_activity').
	                " AS ga WHERE ga.suppliers_id='$suppliers_id' $othersql");

    $pager  = get_pager('merchant.php', array('act' => $action), $count, $page, $size);

    $group_list = get_suppliers_group($suppliers_id, $pager['size'], $pager['start'], $othersql);
    $smarty->assign('pager',  $pager);
    $smarty->assign('action', 'group');

    $smarty->assign('group_list', $group_list);
  $smarty->display('group_merchant.dwt');
}
//elseif ($action == 'get_excel')
//{
  //  @set_time_limit(0);
    //$group_id  = !empty($_GET['id']) ? intval($_GET['id']) : 0;
    /* 文件名称 */
    //$group_filename = 'cards_' . date('Ymd');
    //if (EC_CHARSET != 'gbk')
    //{
      //  $group_filename = ecs_iconv('UTF8', 'GB2312',$group_filename);
    //}
    //header("Content-type: application/vnd.ms-excel; charset=utf-8");
    //header("Content-Disposition: attachment; filename=$group_filename.xls");
    /* 文件标题 */
    /*if (EC_CHARSET != 'gbk')
    {   
	   // echo ecs_iconv('UTF8', 'GB2312', $_LANG['user_name']) ."\t";
		//echo ecs_iconv('UTF8', 'GB2312', $_LANG['user_mobile']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['card_sn']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['card_password']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['past_time']) ."\t\n";
    }
    else
    {
		//echo $_LANG['user_name'] ."\t";
		//echo $_LANG['user_mobile'] ."\t";
        echo $_LANG['card_sn'] ."\t";
        echo $_LANG['card_password'] ."\t";
        echo $_LANG['past_time'] ."\t\n";
    }
    $val = array();
    $sql = "SELECT gc.card_sn, gc.card_password,o.mobile,o.tel,u.user_name,gc.end_date,gc.order_sn ".
           "FROM ".$ecs->table('group_card')." AS gc, ".$ecs->table('order_info')." AS o, ". $ecs->table('users')." AS u " .
           "WHERE gc.order_sn = o.order_sn AND u.user_id=gc.user_id AND gc.is_saled =1 ".
		   "AND gc.group_id = '$group_id'  ORDER BY gc.group_id DESC ";
    $res = $db->query($sql);
    while ($val = $db->fetchRow($res))
    {
		//echo ecs_iconv('UTF8', 'GB2312', $val['user_name']) ."\t";
        //echo $val['mobile'] . "\t";
        echo $val['card_sn'] . "\t";
		echo $val['card_password'] . "\t";
        echo local_date('Y-m-d', $val['end_date']);
        echo "\t\n";
    }
}*/
elseif ($action == 'order')
{  
   $group_id = !empty($_REQUEST['id'])  && intval($_REQUEST['id'])  > 0 ? intval($_REQUEST['id'])  : 0;
   $order_sn = empty($_POST['ordersn']) ? '' : trim($_POST['ordersn']);
   $page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
   $size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
   $othersql = '';
	if ($_SESSION['parent_id'] > 0)
	{	
	  $sql = "SELECT group_id FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE suppliers_id = '$suppliers_id' ";	
      $expand_supp = $GLOBALS['db']->getCol($sql);
	  $othersql = " AND ga.group_id " . db_create_in($expand_supp);
      $suppliers_id = $_SESSION['parent_id'];
	}
   if ($order_sn != '')
   {
      $othersql .= " AND o.order_sn='$order_sn'";
   }
   if ($group_id > 0)
   {
      $othersql .= " AND ga.group_id='$group_id'";
   }	
   $count = $db->getOne("SELECT count(*) FROM " . 
						$ecs->table('order_info')." AS o," .
	                     $ecs->table('group_activity') . " AS ga " .
		      "WHERE o.extension_id=ga.group_id AND ga.suppliers_id= '$suppliers_id' AND o.pay_status =2 $othersql");
    $pager  = get_pager('merchant.php',array('act'=>$action,'id'=>$group_id), $count, $page, $size);
    $order_list = get_suppliers_order($suppliers_id, $pager['size'], $pager['start'], $othersql);
    $smarty->assign('pager',  $pager);
	$smarty->assign('action', $action);

    $smarty->assign('order_list', $order_list);
  $smarty->display('group_merchant.dwt');
}
elseif ($action == 'stats')
{
      $group_id = intval($_GET['id']);
      if ($group_id> 0)
	  {
	    $arr = get_card_stats($group_id);
	    $smarty->assign('stats', $arr['allstats']);
	    $smarty->assign('smallmer', $arr['smallmer']);
	  }
	 $sql = 'SELECT suppliers_name FROM ' . $ecs->table('suppliers') .
            " WHERE suppliers_id = '$suppliers_id'";
	 $suppliers_name = $db->getOne($sql);
	 $smarty->assign('suppliers_name',$suppliers_name); 
	 $smarty->assign('action', $action);
	 $smarty->display('group_merchant.dwt');
} 
else
{  
   $action = 'coupons';
   $group_id = intval($_GET['id']);
   $card_sn = empty($_POST['card_sn']) ? '' : trim($_POST['card_sn']);
   $page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
   $size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
   $where = !empty($card_sn) ? " AND gc.card_sn='$card_sn'" : '';
   if ($group_id > 0)
   {
      $where = " AND ga.group_id='$group_id'";
	  $act = array('act' => $action, 'id' => $group_id);
	}
	else
	{
	  $act = array('act' => $action);
	}
	  $othersql = '';
	if ($_SESSION['parent_id'] > 0)
	{	
	  $sql = "SELECT group_id FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE suppliers_id = '$suppliers_id' ";	
      $expand_supp = $GLOBALS['db']->getCol($sql);
	  $othersql = " AND ga.group_id " . db_create_in($expand_supp);
      $suppliers_id = $_SESSION['parent_id'];
	}
   $count = $db->getOne("SELECT count(*) FROM " . 
	        $GLOBALS['ecs']->table('group_card') . " AS gc," .
		    $GLOBALS['ecs']->table('group_activity') . " AS ga " .
           " WHERE ga.group_id=gc.group_id AND gc.is_saled = 1 $where AND ga.suppliers_id= '$suppliers_id' $othersql");
    $pager  = get_pager('merchant.php', array('act'=>$action), $count, $page, $size);
    $coupons_list = get_suppliers_coupons($suppliers_id, $pager['size'], $pager['start'], $where.$othersql);
    $smarty->assign('pager',  $pager);
	$smarty->assign('action', $action);

    $smarty->assign('coupons_list', $coupons_list);
  $smarty->display('group_merchant.dwt');
}

function get_suppliers_coupons($suppliers_id, $num = 10, $start = 0,$where = '')
{
    /* 取得订单列表 */
    $arr    = array();
    $sql = "SELECT gc.*,o.order_sn FROM " . 
	        $GLOBALS['ecs']->table('group_card') . " AS gc," .
	        $GLOBALS['ecs']->table('order_info') . " AS o," . 
		    $GLOBALS['ecs']->table('group_activity') . " AS ga " .
           " WHERE gc.is_saled = 1 AND o.order_sn=gc.order_sn " .
		   "AND ga.group_id=gc.group_id AND ga.suppliers_id= '$suppliers_id' $where ORDER BY gc.card_id DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
    $now = gmtime();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
	    if ($row['end_date'] > $now)
		{
			if ($row['is_used'] == 0)
			{
				$row['card_stat'] = '0';
			}
			else
			{
			   $row['card_stat'] = '1';
			}
		}
		else
		{
		  $row['card_stat'] = 2;
		}
		$row['card_stat_name'] = $GLOBALS['_LANG']['card_stat'][$row['card_stat']];
 	    $row['end_date'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_date']);
		$row['use_date'] = local_date($GLOBALS['_CFG']['time_format'], $row['use_date']);
		$row['group_url'] = rewrite_groupurl('team.php',array('id' => $row['group_id']));
        $arr[] = $row;
    }

    return $arr;
}
function get_suppliers_group($suppliers_id, $num = 10, $start = 0,$where = '')
{
    /* 取得订单列表 */
    $arr    = array();

    $sql = "SELECT ga.group_id,ga.group_name,ga.start_time,ga.end_time,ga.market_price,ga.goods_type,".
	       "ga.is_finished,ga.upper_orders,ga.lower_orders,ga.ext_info FROM " . 
	       $GLOBALS['ecs']->table('group_activity') . " AS ga " .
		   "WHERE ga.suppliers_id= '$suppliers_id' $where ORDER BY ga.group_id DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
	$group_list = array();
    while ($arr = $GLOBALS['db']->fetchRow($res))
    {
	     $ext_info = unserialize($arr['ext_info']);
        $stat = get_group_buy_stat($arr['group_id']);
        $arr = array_merge($arr, $stat, $ext_info);

        /* 处理价格阶梯 */
        $price_ladder = $arr['price_ladder'];
        if (!is_array($price_ladder) || empty($price_ladder))
        {
            $price_ladder = array(array('amount' => 0, 'price' => 0));
        }
        else
        {
            foreach ($price_ladder AS $key => $amount_price)
            {
                $price_ladder[$key]['formated_price'] = group_price_format($amount_price['price']);
            }
        }
		
        /* 计算当前价 */
        $cur_price  = $price_ladder[0]['price'];    // 初始化
      
        $arr['cur_price']   = $cur_price;
		$arr['formated_cur_price'] = group_price_format($cur_price);
		$arr['formated_market_price'] = group_price_format($arr['market_price']);
        $status = get_group_buy_status($arr);
		$stat = get_group_buy_stat($arr['group_id']);
		$arr = array_merge($arr,$stat);
        $arr['start_time']  = local_date($GLOBALS['_CFG']['date_format'], $arr['start_time']);
        $arr['end_time']    = local_date($GLOBALS['_CFG']['date_format'], $arr['end_time']);
        $arr['cur_status']  = $GLOBALS['_LANG']['gbs'][$status];
		$arr['group_url'] = rewrite_groupurl('team.php',array('id' => $arr['group_id']));
        $group_list[] = $arr;
    }
    return $group_list;
}
function get_suppliers_order($suppliers_id, $num = 10, $start = 0,$where = '')
{
    /* 取得订单列表 */
    $arr    = array();

    $sql = "SELECT o.order_id,o.order_sn, o.goods_amount, o.shipping_fee,o.shipping_status,o.bonus, o.money_paid,o.order_amount,".
	       "o.discount,o.add_time,o.shipping_time,o.pay_time,o.pay_name,o.postscript,ga.goods_name,o.postscript,".
	        "o.consignee,o.address,o.mobile,o.zipcode,o.invoice_no,ga.goods_name FROM " . 
		   $GLOBALS['ecs']->table('order_info')." AS o," .
	       $GLOBALS['ecs']->table('group_activity') . " AS ga " .
		   "WHERE o.extension_id=ga.group_id AND ga.suppliers_id= '$suppliers_id' AND o.pay_status =2 $where ORDER BY o.order_id DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);
	$order_list = array();
    while ($arr = $GLOBALS['db']->fetchRow($res))
    {
		$sql = "SELECT goods_number FROM " . $GLOBALS['ecs']->table('order_goods').
		       " WHERE order_id='$arr[order_id]'";
		$arr['goods_num'] =  $GLOBALS['db']->getOne($sql);
        $arr['add_time']  = local_date($GLOBALS['_CFG']['date_format'], $arr['add_time']);
        $order_list[] = $arr;
    }
    return $order_list;
}
function get_card_stats($group_id)
{
	$day = local_getdate();
    $end_time = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	$oend_time = $end_time - 86400;
	$start_time = local_mktime(0, 0, 0, $day['mon'], $day['mday'], $day['year']);
	$ostart_time = $start_time - 86400;
	$suppliers_id = $_SESSION['suppliers_id'];
	$statarr = array();
	$gsql = "SELECT group_id,suppliers_id,group_name,goods_name,past_time,start_time AS start_date, end_time AS end_date,ext_info FROM " .
             $GLOBALS['ecs']->table('group_activity') . " WHERE group_id='$group_id'";
    $statarr = $GLOBALS['db']->getRow($gsql);
	if ($_SESSION['parent_id'] > 0)
	{
	   if ($statarr['suppliers_id'] != $_SESSION['parent_id'])
	   {
	     return array();
		 exit;
	   }
	   $othersql = " AND suppliers_id='$suppliers_id'";
	}
	else
	{
	   $othersql = '';	
	   if ($statarr['suppliers_id'] != $_SESSION['suppliers_id'])
	   {
	     return array();
		 exit;
	   }    
	}
    $ext_info = unserialize($statarr['ext_info']);
    $statarr = array_merge($statarr, $ext_info);
    $price_ladder = $statarr['price_ladder'];
    if (!is_array($price_ladder) || empty($price_ladder))
    {
        $price_ladder = array(array('amount' => 0, 'price' => 0));
    }
    else
    {
        foreach ($price_ladder as $key => $amount_price)
        {   
            $price_ladder[$key]['formated_price'] = $amount_price['price'];
        }
    }
	$statarr['url'] = rewrite_groupurl('team.php',array('id' => $statarr['group_id']));
	$statarr['formated_start_date'] = local_date('Y-m-d', $statarr['start_date']);
    $statarr['formated_end_date'] = local_date('Y-m-d', $statarr['end_date']);
    $statarr['formated_past_date'] = local_date('Y-m-d', $statarr['past_time']);

    $statarr['price_ladder'] = $price_ladder;
	$statarr['group_price'] = $price_ladder[0]['formated_price'];
    $statarr['formated_group_price']= group_price_format($statarr['group_price']);

	$tsql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND is_used='1' AND use_date >='$start_time' AND use_date <= '$end_time' $othersql";
	$statarr['tcount'] =  $GLOBALS['db']->getOne($tsql);
	$statarr['tprice'] = group_price_format($statarr['tcount']*$statarr['group_price']);
    $osql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND is_used='1' AND use_date >='$ostart_time' AND use_date <= '$oend_time' $othersql";
	$statarr['ocount'] =  $GLOBALS['db']->getOne($osql);
    $statarr['oprice'] = group_price_format($statarr['ocount']*$statarr['group_price']);

    $assql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND is_used='1' $othersql";
	$statarr['ascount'] =  $GLOBALS['db']->getOne($assql);
	$statarr['asprice'] = group_price_format($statarr['ascount']*$statarr['group_price']);

	$asql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' $othersql";
	$statarr['acount'] =  $GLOBALS['db']->getOne($asql);		   		   
    $ausql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND is_saled='1' AND is_used ='0' $othersql";
	$statarr['nucount'] =  $GLOBALS['db']->getOne($ausql);		   		   

    $sql = "SELECT suppliers_id,suppliers_name FROM " . $GLOBALS['ecs']->table('suppliers') . " WHERE parent_id='$suppliers_id'";
	$res = $GLOBALS['db']->query($sql);
	$card_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
		$smallid = $row['suppliers_id'];
	    $tsql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND suppliers_id ='$smallid' AND is_used='1' AND use_date >='$start_time' AND use_date <= '$end_time'";
	    $row['tcount'] = $GLOBALS['db']->getOne($tsql);   
        $osql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_card') .
		       " WHERE group_id='$group_id' AND is_used='1' AND suppliers_id ='$smallid'";
		$row['ocount'] = $GLOBALS['db']->getOne($osql); 
		$row['oprice'] = group_price_format($row['ocount']*$statarr['group_price']);
        $card_list[] = $row;
	}
	return array('smallmer' => $card_list,'allstats'=>$statarr);
}
?>