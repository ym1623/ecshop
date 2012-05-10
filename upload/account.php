<?php

/**
 * ECGROUPON 会员中心
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['formwork'];

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/account.php');

$user_id = $_SESSION['user_id'];
if ($user_id <= '0')
{
	$indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
    ecs_header("Location: $url\n");
    exit;
}
$action  = trim($_REQUEST['act']);
$action_arr = array('settings','act_settings','charge','credit','act_charge','logout','pay','address','act_address','cancel');
if (!in_array($action, $action_arr))
{
  $action = 'settings';
}
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];

/* 个人资料页面 */
assign_public($city_id);
if ($action == 'settings')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $user_info = get_profile($user_id);
	$sql = 'SELECT city_id FROM '. $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
    $user_info['city_id'] = $db->getOne($sql);
	$smarty->assign('action', $action);
	$smarty->assign('menu', 'settings');
    $smarty->assign('profile', $user_info);
    $smarty->display('group_account.dwt');
}

/* 修改个人资料的处理 */
elseif ($action == 'act_settings')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $other['mobile_phone'] = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
    $city_id = intval($_POST['city_id']);
	$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    if (!empty($mobile_phone) && !preg_match('/^[\d-\s]+$/', $mobile_phone))
    {
        show_group_message($_LANG['mobile_phone_invalid']);
    }


    $profile  = array(
        'user_id'  => $user_id,
        'other'    => isset($other) ? $other : array()
        );

    if ($new_password != '')
	{
		$user->edit_user(array('username'=> $_SESSION['user_name'], 'password'=>$new_password));
	}
    if (edit_profile($profile))
    {   
	    $sql = 'UPDATE '. $GLOBALS['ecs']->table('users') . " SET city_id= '$city_id' WHERE user_id = '$user_id'";
        $db->query($sql);
        show_group_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'], 'account.php?act=settings', 'info');
    }
    else
    {
        if ($user->error == ERR_EMAIL_EXISTS)
        {
            $msg = sprintf($_LANG['email_exist'], $profile['email']);
        }
        else
        {
            $msg = $_LANG['edit_profile_failed'];
        }
        show_group_message($msg, '', '', 'info');
    }
}

/* 会员预付款界面 */
elseif ($action == 'charge')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type ='" . SURPLUS_SAVE . "'" ;
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }
   
    //获取余额记录
    $account_log = get_pay_record($user_id, $pager['size'], $pager['start']);
	
	
    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
	$smarty->assign('confirm_remove_account',$_LANG['confirm_remove_account']);
    $smarty->assign('pager',          $pager);
    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $account);
	$smarty->assign('action', $action);
	$smarty->assign('menu', 'credit');
    $smarty->display('group_account.dwt');
}

elseif ($action == 'credit')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'user_money';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
	$size = 10;
    $pager = get_pager('account.php', array('act' => $action), $record_count, $page, $size);

    //获取剩余余额
    //$surplus_amount = get_user_surplus($user_id);
	$sql = "SELECT user_money FROM " . $GLOBALS['ecs']->table('users') .
            " WHERE user_id = '$user_id'";
    $surplus_amount = $db->getOne($sql);

    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? $_LANG['account_inc'] : $_LANG['account_dec'];
        $row['user_money'] = group_price_format(abs($row['user_money']));
        $row['frozen_money'] = group_price_format(abs($row['frozen_money']));
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', group_price_format($surplus_amount));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
	$smarty->assign('action', $action);
	$smarty->assign('menu', 'credit');
    $smarty->display('group_account.dwt');
}


/* 对会员余额申请的处理 */
elseif ($action == 'act_charge')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0)
    {
        show_group_message($_LANG['amount_gt_zero']);
    }

    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
            'amount'       => $amount
    );
    if ($surplus['payment_id'] <= 0)
    {
        show_group_message($_LANG['select_payment_pls']);
    }

    include_once(ROOT_PATH .'includes/lib_payment.php');

        //获取支付方式名称
    $payment_info = array();
    $payment_info = payment_info($surplus['payment_id']);
    $surplus['payment'] = $payment_info['pay_name'];

     if ($surplus['rec_id'] > 0)
     {
            //更新会员账目明细
         $surplus['rec_id'] = update_user_account($surplus);
      }
      else
      {
           //插入会员账目明细
          $surplus['rec_id'] = insert_user_account($surplus, $amount);
      }

        //取得支付信息，生成支付代码
       $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号, 不足的时候补0
       $order = array();
       $order['order_sn']       = $surplus['rec_id'];
       $order['user_name']      = $_SESSION['user_name'];
       $order['surplus_amount'] = $amount;

        //计算支付手续费用
       $payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $amount + $payment_info['pay_fee'];

        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee', group_price_format($payment_info['pay_fee']));
        $smarty->assign('amount',  group_price_format($amount));
        $smarty->assign('order',   $order);
		$smarty->assign('action', $action);
	    $smarty->assign('menu', 'credit');
        $smarty->display('group_account.dwt');
}

elseif ($action == 'pay')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');

    //变量初始化
    $surplus_id = isset($_GET['id'])  ? intval($_GET['id'])  : 0;

    if ($surplus_id == 0)
    {
        ecs_header("Location: account.php?act=credit\n");
        exit;
    }
    //获取单条会员帐目信息
    $order = array();
    $order = get_surplus_info($surplus_id);
    $sql = 'SELECT pay_id FROM ' .$GLOBALS['ecs']->table('payment').
                   " WHERE pay_name = '$order[payment]' AND enabled = 1";
    $pid = $GLOBALS['db']->getOne($sql);
    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $order);
	$smarty->assign('pid',     $pid);
    $smarty->assign('action',  'charge');
	$smarty->assign('menu', 'credit');
    $smarty->display('group_account.dwt');
}
elseif ($action == 'address')
{
	$arr = array();
    if ($user_id > 0)
    {
       $sql = "SELECT ua.* ".
                    " FROM " . $GLOBALS['ecs']->table('user_address') . "AS ua, ".$GLOBALS['ecs']->table('users').' AS u '.
                    " WHERE u.user_id='$user_id' AND ua.address_id = u.address_id limit 1";
        $consignee = $GLOBALS['db']->getRow($sql);
    }
	if (!empty($consignee))
	{
	  $city_list   = get_regions(2, $consignee['province']);
      $district_list = get_regions(3, $consignee['city']);
      $smarty->assign('city_list',     $city_list);
      $smarty->assign('district_list', $district_list);
      $smarty->assign('show_district', '1');
	}
   	$smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_country',       $_CFG['shop_country']);
    $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
	$smarty->assign('consignee',$consignee);
	$smarty->assign('action', $action);
	$smarty->assign('menu', 'address');
    
    $smarty->display('group_account.dwt');
}

/* 修改个人资料的处理 */
elseif ($action == 'act_address')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    $consignee = array(
            'address_id'    => empty($_POST['address_id']) ? 0  : intval($_POST['address_id']),
            'country'       => empty($_POST['country'])    ? '' : intval($_POST['country']),
            'province'      => empty($_POST['province'])   ? '' : intval($_POST['province']),
            'city'          => empty($_POST['city'])       ? '' : intval($_POST['city']),
			'district'      => empty($_POST['district'])   ? '' : intval($_POST['district']),
            'consignee'     => empty($_POST['consignee'])  ? '' : trim($_POST['consignee']),
            'address'       => empty($_POST['address'])    ? '' : trim($_POST['address']),
            'zipcode'       => empty($_POST['zipcode'])    ? '' : make_semiangle(trim($_POST['zipcode'])),
            'mobile'        => empty($_POST['mobile'])     ? '' : make_semiangle(trim($_POST['mobile']))
        );
    $consignee['user_id'] = $_SESSION['user_id'];
    save_consignee($consignee, true);
	$_SESSION['flow_consignee'] = stripslashes_deep($consignee);
    show_group_message('修改成功!', '', '', 'info');
  
}
elseif ($action == 'cancel')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        ecs_header("Location: account.php?act=charge\n");
        exit;
    }

    $result = del_user_account($id, $user_id);
    if ($result)
    {
        ecs_header("Location: account.php?act=charge\n");
        exit;
    }
}

elseif ($action == 'logout')
{
    $indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);

    if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'account.php') ? $url : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    $user->logout();
    $ucdata = empty($user->ucdata)? "" : $user->ucdata;
    show_group_message($_LANG['logout'] . $ucdata, array($_LANG['back_up_page'], $_LANG['back_home_lnk']), array($back_act, $url), 'info');
}

function get_pay_record($user_id, $num, $start)
{
    
	$account_log = array();
    $sql = 'SELECT * FROM ' .$GLOBALS['ecs']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type ='" . SURPLUS_SAVE .
           "' ORDER BY add_time DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $num, $start);

    if ($res)
    {
        while ($rows = $GLOBALS['db']->fetchRow($res))
        {
            $rows['add_time']         = local_date($GLOBALS['_CFG']['date_format'], $rows['add_time']);
            $rows['admin_note']       = nl2br(htmlspecialchars($rows['admin_note']));
            $rows['short_admin_note'] = ($rows['admin_note'] > '') ? sub_str($rows['admin_note'], 30) : 'N/A';
            $rows['user_note']        = nl2br(htmlspecialchars($rows['user_note']));
            $rows['short_user_note']  = ($rows['user_note'] > '') ? sub_str($rows['user_note'], 30) : 'N/A';
            $rows['pay_status']       = ($rows['is_paid'] == 0) ? $GLOBALS['_LANG']['un_confirm'] : $GLOBALS['_LANG']['is_confirm'];
            $rows['amount']           = group_price_format(abs($rows['amount']));

            /* 会员的操作类型： 冲值，提现 */
            if ($rows['process_type'] == 0)
            {
                $rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
            }
            else
            {
                $rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
            }
            /* 如果是预付款而且还没有付款, 允许付款 */
            if ($rows['is_paid'] == 0)
            {
			               /* 支付方式的ID */
                $rows['handle'] = '<a href="account.php?act=pay&id='.$rows['id'].'">'.$GLOBALS['_LANG']['pay'].'</a>';
            }

            $account_log[] = $rows;
        }

        return $account_log;
    }
     else
    {
         return false;
    }
}

?>