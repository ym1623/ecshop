<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/phone.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['formwork'];
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
assign_public($city_id);
if ($_REQUEST['act'] == 'add_phone')
{
    $do = trim($_REQUEST['doi']);
    include('includes/cls_json.php');
	include_once('includes/cls_sms.php');
    $sms = new sms();
    $json   = new JSON;
	$phone = trim($_POST['phone']);
    $result = array('error' => '0', 'content' => '', 'msg' => '', 'phone' => $phone, 'do' => '');
    if (empty($phone) && !preg_match('/^[\d-\s]+$/', $phone) )
    {
		$result['msg'] = '手机号码不是一个有效号码';
		$result['error'] = 1;
		die($json->encode($result));
    }
	if (($do == 'add' || $do == 'del') && gd_version() > 0)
    {
	        $result['dovalue'] = $do;
            if (empty($_POST['captcha']))
            {
				$result['error'] = 1;
				$result['msg'] = $_LANG['invalid_captcha'];
				die($json->encode($result));
            }

            /* 检查验证码 */
            include_once('includes/cls_captcha.php');

            $validator = new captcha();
            if (!$validator->check_word($_POST['captcha']))
            {
				$result['error'] = 1;
				$result['msg'] = $_LANG['invalid_captcha'];
				die($json->encode($result));
            }
    }
	$ck = $db->getRow("SELECT * FROM " . $ecs->table('phone_list') . " WHERE phone = '$phone'");
    if ($do == 'add')
    {   
        if (empty($ck))
        {
            $hash = substr(md5(time()), 1, 5);
            $sql = "INSERT INTO " . $ecs->table('phone_list') . " (phone, stat, hash, city_id) VALUES ('$phone', 0, '$hash','$city_id')";
            $db->query($sql);
			$msg = $_CFG['group_shopname'].'欢迎您短信订阅，请您输入认证码:' . $hash . ',进行验证';
			$sms->send($phone, $msg, 0);
		    //die($json->encode($result));
	     }
        elseif ($ck['stat'] == 1)
        {
			$result['error'] = 1;
            $info = sprintf($_LANG['phone_alreadyin_list'], $phone);
			$result['msg'] = $info;
        }
        else
        {
            $hash = substr(md5(time()),1 , 5);
            $sql = "UPDATE " . $ecs->table('phone_list') . "SET hash = '$hash' WHERE phone = '$phone'";
            $db->query($sql);
			$msg = $_CFG['group_shopname'].'欢迎您短信订阅，请您输入认证码:' . $hash . ',进行验证';
			$sms->send($phone, $msg, 0);
        }
      	    $result['doi'] = $do;
	        die($json->encode($result));
    }
    elseif ($do == 'del')
    {
        if (empty($ck))
        {
		    $result['error'] = 1;
            $info = sprintf($_LANG['phone_notin_list'], $phone);
        }
        elseif ($ck['stat'] == 1)
        {
            $hash = substr(md5(time()),1,5);
            $sql = "UPDATE " . $ecs->table('phone_list') . "SET hash = '$hash' WHERE phone = '$phone'";
            $db->query($sql);
			$msg = $_CFG['group_shopname'].'感谢您的关注，请您输入认证码:' . $hash . ',取消订阅';
			$sms->send($phone, $msg, 0);
		    //die($json->encode($result));

        }
        else
        {
		    $result['error'] = 1;
            $info = $_LANG['phone_not_alive'];
        }
		$result['msg'] = $info;
	    $result['doi'] = $do;
     	die($json->encode($result));

    }
    elseif ($do == 'add_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['phone_notin_list'], $phone);
        }
        elseif ($ck['stat'] == 1)
        {
			
            $info = $_LANG['phone_succ'];
        }
        else
        {
            if ($_POST['phone_hash'] == $ck['hash'])
            {
                $sql = "UPDATE " . $ecs->table('phone_list') . "SET stat = 1 WHERE phone = '$phone'";
                $db->query($sql);
                $info = $_LANG['phone_succ'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
	    $result['error'] = 1;
		$result['msg'] = $info;
	   	die($json->encode($result));	
    }
    elseif ($do == 'del_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['phone_invalid'], $phone);
        }
        elseif ($ck['stat'] == 1)
        {
            if ($_POST['phone_hash'] == $ck['hash'])
            {
                $sql = "DELETE FROM " . $ecs->table('phone_list') . "WHERE phone = '$phone'";
                $db->query($sql);
                $info = $_LANG['phone_canceled'];
            }
            else
            {    
                $info = $_LANG['hash_wrong'];
            }
        }
        else
        {
            $info = $_LANG['phone_not_alive'];
        }
	    $result['error'] = 1;
		$result['msg'] = $info;
	   	die($json->encode($result));	
    }
}

?>