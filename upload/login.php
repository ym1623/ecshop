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

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
assign_public($city_id);

if ($_POST['act'] == 'act_login')
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
    if ($user->login($username, $password,isset($_POST['remember'])))
    {  
        update_user_info();
        //recalculate_price();
		$indexurl = array('team.php','index.php');
	    $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
        ecs_header("Location: $url\n");
        exit; 
    }
    else
    {   
        show_group_message($_LANG['login_failure'], $_LANG['relogin_lnk'], 'login.php', 'error');
    }
}
/* 密码找回-->修改密码界面 */
elseif ($_GET['act'] == 'get_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    if (isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_group_message($_LANG['parm_error'], $_LANG['back_home_lnk'], 'login.php', 'info');
        }

        $smarty->assign('uid',    $uid);
        $smarty->assign('code',   $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('login.dwt');
    }
    else
    {
        //显示用户名和email表单
		$smarty->assign('action', 'get_password');
        $smarty->display('login.dwt');
    }
}

/* 发送密码修改确认邮件 */
elseif ($_POST['act'] == 'send_pwd_email')
{
    /* 初始化会员用户名和邮件地址 */	
    $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $email     = !empty($_POST['email'])     ? trim($_POST['email'])     : '';

    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);
    if ($user_info && $user_info['email'] == $email)
    {
        //生成code

        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);
        //发送邮件的函数
        if (send_password_email($user_info['user_id'], $user_name, $email, $code))
        {
			$indexurl = array('team.php','index.php');
	        $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);

            show_group_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], $url, 'info');
        }
        else
        {
            show_group_message($_LANG['fail_send_password'], $_LANG['back_page_up'], 'login.php', 'info');
        }
    }
    else
    {
        show_group_message($_LANG['username_no_email'], $_LANG['back_page_up'], 'login.php', 'info');
    }
}
elseif ($_POST['act'] == 'act_edit_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id      = isset($_POST['uid'])  ? intval($_POST['uid']) : $user_id;
    $code         = isset($_POST['code']) ? trim($_POST['code'])  : '';

    if (strlen($new_password) < 6)
    {
        show_group_message($_LANG['passport_js']['password_shorter']);
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {
        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
            $user->logout();
            show_group_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'login.php?act=login', 'info');
        }
        else
        {
            show_group_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], 'login.php', 'info');
        }
    }
    else
    {
        show_group_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], 'login.php', 'info');
    }

}
else
{
	if (empty($back_act))
    {
       if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
       {
		  $indexurl = array('team.php','index.php');
	      $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
         $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'login.php') ? $url : $GLOBALS['_SERVER']['HTTP_REFERER'];
       }
       else
      {
         $back_act = 'login.php';
      }
    }
   $smarty->assign('loginopen',get_loginopen());	
   $smarty->assign('action', 'login');	
   $smarty->display('login.dwt');

}

function send_password_email($uid, $user_name, $email, $code)
{
    if (empty($uid) || empty($user_name) || empty($email) || empty($code))
    {
        ecs_header("Location: login.php?act=get_password\n");

        exit;
    }

    /* 设置重置邮件模板所需要的内容信息 */
    $template    = get_mail_template('send_password');
    $reset_email = $GLOBALS['ecs']->url() . 'login.php?act=get_password&uid=' . $uid . '&code=' . $code;

    $GLOBALS['smarty']->assign('user_name',   $user_name);
    $GLOBALS['smarty']->assign('reset_email', $reset_email);
    $GLOBALS['smarty']->assign('shop_name',   $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date',   date('Y-m-d'));
    $GLOBALS['smarty']->assign('sent_date',   date('Y-m-d'));

    $content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

    /* 发送确认重置密码的确认邮件 */
    if (send_mail($user_name, $email, $template['template_subject'], $content, $template['is_html']))
    {
        return true;
    }
    else
    {
        return false;
    }
}

?>