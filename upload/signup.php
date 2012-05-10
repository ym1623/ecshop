<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];

require_once(ROOT_PATH . 'includes/lib_group.php');
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
assign_public($city_id);

if ($_POST['act'] == 'act_register')
{
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $email    = isset($_POST['useremail']) ? trim($_POST['useremail']) : '';
        $other['mobile_phone'] = isset($_POST['mobile_phone']) ? $_POST['mobile_phone'] : '';
        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
		$city_id = intval($_POST['city_id']);
        if (strlen($username) < 3)
        {
            show_group_message($_LANG['passport_js']['username_shorter']);
        }

        if (strlen($password) < 6)
        {
            show_group_message($_LANG['passport_js']['password_shorter']);
        }

        if (strpos($password, ' ') > 0)
        {
            show_group_message($_LANG['passwd_balnk']);
        }

        if (register($username, $password, $email, $other) !== false)
        {
			if ($_SESSION['user_id'] > 0)
			{
			   $sql = 'UPDATE '. $GLOBALS['ecs']->table('users') . " SET city_id= '$city_id' WHERE user_id = '$_SESSION[user_id]'";
               $db->query($sql);
	          if ($_CFG['group_member_email'] == 1)
			  {
			    send_checking_email($_SESSION['user_id']);
			  }
			}
			if ($_POST['is_book'])
			{	
			  $ck = $db->getOne("SELECT * FROM " . $ecs->table('email_list') . " WHERE email = '$email'");
			  if (!$ck)
			  {
		       $hash = substr(md5(time()), 1, 10);
               $sql = "INSERT INTO " . $ecs->table('email_list') . " (email, stat, hash, city_id) VALUES ('$email', 0, '$hash','$city_id')";
               $db->query($sql);
			  }
			}
           	$indexurl = array('team.php','index.php');
	        $url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
            ecs_header("Location: $url\n");
            exit; 
        }
        else
        {   
            show_group_message($_LANG['sign_up'],$_LANG['profile_lnk'], 'signup.php');
        }
}

/* 验证用户注册邮件 */
elseif ($_GET['act'] == 'validate_email')
{
    $hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
    if ($hash)
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');
        $id = register_hash('decode', $hash);
        if ($id > 0)
        {
            $sql = "UPDATE " . $ecs->table('users') . " SET is_validated = 1 WHERE user_id='$id'";
            $db->query($sql);
            $sql = 'SELECT user_name, email FROM ' . $ecs->table('users') . " WHERE user_id = '$id'";
            $row = $db->getRow($sql);
            show_group_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'account.php');
        }
    }
    show_group_message($_LANG['validate_fail']);
}

/* 验证用户注册用户名是否可以注册 */
elseif ($_GET['act'] == 'check_user')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $username = trim($_POST['username']);
    $username = json_str_iconv($username);

    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}

/* 验证用户邮箱地址是否被注册 */
elseif($_GET['act'] == 'check_email')
{
    $email = trim($_POST['useremail']);
    if ($user->check_email($email))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}
else
{ 
   $smarty->assign('loginopen',get_loginopen());	
   $smarty->display('signup.dwt');
}

/**
 *  发送激活验证邮件
 *
 * @access  public
 * @param   int     $user_id        用户ID
 *
 * @return boolen
 */
function send_checking_email($user_id)
{
    /* 设置验证邮件模板所需要的内容信息 */
    $template    = get_mail_template('register_validate');
    $hash = register_hash('encode', $user_id);
    $validate_email = $GLOBALS['ecs']->url() . 'signup.php?act=validate_email&hash=' . $hash;

    $sql = "SELECT user_name, email FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
    $row = $GLOBALS['db']->getRow($sql);

    $GLOBALS['smarty']->assign('user_name',         $row['user_name']);
    $GLOBALS['smarty']->assign('validate_email',    $validate_email);
    $GLOBALS['smarty']->assign('shop_name',         $GLOBALS['_CFG']['shop_name']);
    $GLOBALS['smarty']->assign('send_date',         date($GLOBALS['_CFG']['date_format']));

    $content = $GLOBALS['smarty']->fetch('str:' . $template['template_content']);

    /* 发送确认重置密码的确认邮件 */
    if (send_mail($row['user_name'], $row['email'], $template['template_subject'], $content, $template['is_html']))
    {
        return true;
    }
    else
    {
        return false;
    }
}

?>