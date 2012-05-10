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
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

$action = trim($_GET['act']);
$user_id = $_SESSION['user_id'];
if ($user_id <= '0')
{
	 $indexurl = array('team.php','index.php');
	 $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
     ecs_header("Location: $url\n");
     exit; 
}
$now = gmtime();
$where = '';
if ($action == 'all')
{
	  //$where = " AND is_used = '0'";
}
elseif($action == 'used')
{
	$where = " AND is_used = '1'";
}
elseif($action == 'expiring')
{
	$where = " AND is_used = '0' AND end_date >= '$now'";
}
elseif($action == 'expired')
{
	$where = " AND end_date < '$now'";
}
elseif($action == 'print')
{
	    $card_id = trim($_GET['card_id']);
		$sql = "SELECT card_password,card_sn,group_id,order_sn,user_id FROM " . $ecs->table('group_card').
		       " WHERE card_id='$card_id' AND user_id='$user_id' AND is_used='0'";
		$card_arr = $db->getRow($sql);
		if (!empty($card_arr))
		{
		   $order_sn = $card_arr['order_sn'];	
		   $sql = "SELECT consignee,pay_status,mobile,consignee,tel,email FROM " . $ecs->table('order_info') . 
		          " WHERE order_sn='$order_sn' AND user_id='$user_id'";
		  $order = $db->getRow($sql);
		  $group_id = $card_arr['group_id'];
		  $sql = "SELECT group_name, past_time,suppliers_id FROM " . $ecs->table('group_activity') . " WHERE group_id='$group_id'";
		  $group_arr = $db->getRow($sql);
		  $group_arr['past_time']=local_date('Y-m-d', $group_arr['past_time']);
		  if ($group_arr['suppliers_id']> 0)
		  {
		   $sql = "SELECT * FROM " . $ecs->table('suppliers') . " WHERE suppliers_id='$group_arr[suppliers_id]'";
		   $suppliers_arr = $db->getRow($sql); 
		   $smarty->assign('suppliers_arr', $suppliers_arr);
		  }
		  $smarty->assign('group_cardname',  $GLOBALS['_CFG']['group_cardname']);
		  $smarty->assign('card_arr', $card_arr);
		  $smarty->assign('order',   $order);
		  $smarty->assign('group_arr', $group_arr);
	    }
        $smarty->display('group_print.dwt');
		exit;
}
elseif($action == 'send_sms')
{
       	include('includes/cls_json.php');
        $json   = new JSON;
	    $card_id = trim($_POST['card_id']);
		$sql = "SELECT card_password,card_sn,group_id,order_sn,user_id,end_date,send_num,is_saled FROM " . $ecs->table('group_card').
		       " WHERE card_id='$card_id' AND user_id='$user_id' AND is_used='0'";	   
		$card_arr = $db->getRow($sql);
		if (!empty($card_arr))
		{
		   if ($GLOBALS['_CFG']['send_sms_num'] > 0 && $card_arr['send_num'] >= $GLOBALS['_CFG']['send_sms_num'])
		   {
		     $errormsg = '您的短信发送次数已达到最高限制:'.$GLOBALS['_CFG']['send_sms_num'] . ',请不要频繁发送短信!';
             die($json->encode($errormsg));
		   }
		   $order_sn = $card_arr['order_sn'];	
		   $sql = "SELECT mobile FROM " . $ecs->table('order_info') . 
		          " WHERE order_sn='$order_sn' AND user_id='$user_id'";
		  $mobile = $db->getOne($sql);
		  if (!empty($_POST['mobile']) && $_POST['mobile'] != $mobile)
		  {
			 $mobile =  trim($_POST['mobile']);
		    $sql = "UPDATE " . $ecs->table('order_info') . " SET mobile='$mobile' WHERE order_sn='$order_sn' AND user_id='$user_id'" ;
			 $db->query($sql);
		  }
		  $group_id = $card_arr['group_id'];
		  $sql = "SELECT goods_name FROM " . $ecs->table('group_activity') . " WHERE group_id='$group_id'";
		  $goods_name = $db->getOne($sql);
          include_once('includes/cls_sms.php');
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
		            $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET send_num=send_num+1 WHERE card_id='$card_id'";
	                $GLOBALS['db']->query($sql);
				    $errormsg = '短信发送成功!';
				    die($json->encode($errormsg));
		     }
			 else
			 {
				$errormsg = '短信发送失败,请联系系统管理员!';
				die($json->encode($errormsg));
			 }
		  }
		  else
		  {
		  
		  }
	    }
}
elseif($action == 'make_sms')
{
	    $card_id = trim($_POST['card_id']);
		$sql = "SELECT order_sn FROM " . $ecs->table('group_card').
		       " WHERE card_id='$card_id' AND user_id='$user_id' AND is_used='0'";
		$order_sn = $db->getOne($sql);
		$sql = "SELECT mobile FROM " . $ecs->table('order_info') . 
		          " WHERE order_sn='$order_sn' AND user_id='$user_id'";
		$mobile = $db->getOne($sql);
		$smarty->assign('mobile',$mobile);
		$smarty->assign('card_id',$card_id);
	    $pmesstxt = $smarty->fetch('library/group_pmess.dwt');
		include('includes/cls_json.php');
        $json   = new JSON;
        die($json->encode($pmesstxt));
}

else
{  
	$action = 'coupons';
	$where = " AND is_used = '0' AND end_date >= $now";
}

$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
$page = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
$size = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('group_card').
	                " WHERE is_saled = 1 AND user_id='$user_id' $where");

    $pager  = get_pager('coupons.php', array('act' => $action), $record_count, $page, $size);

    $coupons_list = get_coupons_list($user_id, $pager['size'], $pager['start'], $where);
    $smarty->assign('pager',  $pager);
	$smarty->assign('action', $action);
	$smarty->assign('menu', 'coupons');
    assign_public($city_id);
    $smarty->assign('coupons_list', $coupons_list);
$smarty->display('group_coupons.dwt');

/**
 *  获取用户指定范围的订单列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_coupons_list($user_id, $num = 10, $start = 0,$where = '')
{
    /* 取得订单列表 */
    $arr    = array();

    $sql = "SELECT gc.*,ga.group_id,ga.group_image,is_finished FROM " . $GLOBALS['ecs']->table('group_card') . " AS gc,"
	       .  $GLOBALS['ecs']->table('group_activity') . " AS ga".
           " WHERE gc.user_id = '$user_id' AND gc.is_saled = 1 AND gc.group_id=ga.group_id $where ORDER BY card_id DESC";
    $res = $GLOBALS['db']->SelectLimit($sql, $num, $start);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
 	    $row['end_date'] = local_date($GLOBALS['_CFG']['time_format'], $row['end_date']);
		$row['use_date'] = local_date($GLOBALS['_CFG']['time_format'], $row['use_date']);
		$row['group_url'] = rewrite_groupurl('team.php',array('id' => $row['group_id']));
        $arr[] = $row;
    }
    return $arr;
}

?>