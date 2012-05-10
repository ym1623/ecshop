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
$smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['formwork'];
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
assign_public($city_id);
if ($_REQUEST['act'] == 'add_email')
{
    $do = $_REQUEST['do'];
    if($do == 'add' || $do == 'del')
    {
        if(isset($_SESSION['last_email_query']))
        {
            if(time() - $_SESSION['last_email_query'] <= 30)
            {
			   show_group_message($_LANG['order_query_toofast'], $_LANG['back_home_lnk'], rewrite_groupurl('subscribe.php'));
            }
        }
        $_SESSION['last_email_query'] = time();
    }
    $city_id = trim($_POST['city_id']); 
    $email = trim($_REQUEST['email']);
    $email = htmlspecialchars($email);
    if (!is_email($email))
    {
        $info = sprintf($_LANG['email_invalid'], $email);
	    show_group_message($info, $_LANG['back_home_lnk'], rewrite_groupurl('subscribe.php'));

    }
	$ck = $db->getRow("SELECT * FROM " . $ecs->table('email_list') . " WHERE email = '$email'");
    if ($do == 'add')
    {   
        if (empty($ck))
        {
            $hash = substr(md5(time()), 1, 10);
            $sql = "INSERT INTO " . $ecs->table('email_list') . " (email, stat, hash, city_id) VALUES ('$email', 0, '$hash','$city_id')";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "subscribe.php?act=add_email&do=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['group_shopname'], $url, $url, $_CFG['group_shopname'], local_date('Y-m-d')), 1);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = sprintf($_LANG['email_alreadyin_list'], $email);
        }
        else
        {
            $hash = substr(md5(time()),1 , 10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_re_check'];
            $url = $ecs->url() . "subscribe.php?act=add_email&do=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email,$_CFG['group_shopname'], $url, $url, $_CFG['group_shopname'], local_date('Y-m-d')), 1);
        }
    }
    elseif ($do == 'del')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $hash = substr(md5(time()),1,10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "subscribe.php?act=add_email&do=del_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['group_shopname'], $url, $url, $_CFG['group_shopname'], local_date('Y-m-d')), 1);
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
    }
    elseif ($do == 'add_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = $_LANG['email_checked'];
        }
        else
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "UPDATE " . $ecs->table('email_list') . "SET stat = 1 WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_checked'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
    }
    elseif ($do == 'del_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_invalid'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "DELETE FROM " . $ecs->table('email_list') . "WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_canceled'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
    }
  	$indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);	
   show_group_message($info, $_LANG['back_home_lnk'], $url);
}
else
{ 
  $smarty->assign('where', 'subscribe');
  $smarty->assign('group_city', get_group_city());
  $smarty->assign('shop_notice',     $_CFG['group_notice']); 

  $smarty->display('group_subscribe.dwt');
}

?>