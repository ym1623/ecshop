<?php
/*
 * 用户认证后，获得的access_token以及用户信息
 */

//echo "用户认证通过后，回调相应的页面."; 
header("Content-Type: text/html;charset=utf-8");
session_start(); 
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require('./init.php');
require_once(ROOT_PATH . 'includes/lib_passport.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$apparr = get_loginconfig('tuan800');
$indexurl = array('team.php','index.php');
$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);
if (empty($apparr))
{
  echo '请先开通团800导航的一站通登录,获取APP_KEY与APP_SECRET!';
  echo '<br>','<a href='."http://".$_SERVER['HTTP_HOST'].'/'.$url.'>返回首页';
  exit;
}
define("APP_KEY",$apparr['app_key']);
define("APP_SECRET",$apparr['app_secret']);

include_once(ROOT_PATH . 'oauth/tuan800/lib/TuanAuth.php');
$h = new TuanAuth($_SESSION['request_token']['oauth_token'],$_SESSION['request_token']['oauth_token_secret']);
$accessToken   = $h->getAccessToken($_REQUEST['oauth_verifier']) ;

$oauth_token  = $accessToken["oauth_token"];
$oauth_secret = $accessToken["oauth_token_secret"];
$_SESSION["access_token"] =array('oauth_token'=>$oauth_token,'oauth_token_secret'=>$oauth_secret);
$userinfo_arr   = $h->get("http://api.tuan800.com/oauth/oauthapi/userinfo/userInfo.json",null) ;
if(!empty($oauth_token) && !empty($oauth_secret))
{
	
	 $username = $userinfo_arr['userInfo']['userName'];
    if ($username != '')
   {   
     if ($user->check_user($username))
    {
	   	$sql = 'SELECT user_id FROM '. $GLOBALS['ecs']->table('users') . "  WHERE user_name = '$username'";
        $user_id=$db->getOne($sql);
	    $pass = $user_id . $apparr['app_encrypt'];
      if ($user->login($username, $pass))
	  {	
	    //$_SESSION['qid'] = $userinfo_arr[0];
	  }
    }
    else
   {
	  $pass = '12313123';
	  $email = str_pad(mt_rand(88888888, 99999999), 8, '0', STR_PAD_LEFT).'@126.com';
	 if (register($username,$pass, $email) !== false)
	 {
		// $_SESSION['qid'] = $userinfo_arr[1];
		 $pass = $_SESSION['user_id'] . $apparr['app_encrypt'];
		 $pass = $user->compile_password(array('password'=>$pass));
		 $aliss_name = 'tuan800'. $_SESSION['user_id'];
		 $sql = "UPDATE " .  $ecs->table('users') . " SET password='$pass',aliss_name='$aliss_name' WHERE user_id='$_SESSION[user_id]'";
		 $db->query($sql);
	 }
    }
  }
}
$surl = "http://" . $_SERVER['HTTP_HOST'].'/'.$url;
ecs_header("Location: $surl\n");
exit;
?>
