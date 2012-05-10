<?php
/*
 * 用户认证后，获得的access_token以及用户信息
 */
header("Content-Type: text/html;charset=utf-8");
session_start(); 
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require('./init.php');
require_once(ROOT_PATH . 'includes/lib_passport.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$apparr = get_loginconfig('2345');
$indexurl = array('team.php','index.php');
$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);

if (empty($apparr))
{
  echo '请先开通2345导航的一站通登录,获取APP_KEY与APP_SECRET!';
  echo '<br>','<a href='."http://".$_SERVER['HTTP_HOST'].'/'.$url.'>返回首页';
  exit;
}
include_once(ROOT_PATH . 'oauth/2345/lib/Tuan2345Auth.php');
include_once(ROOT_PATH . "oauth/2345/lib/Tuan2345Client.php");

define("APP_KEY",$apparr['app_key']);
define("APP_SECRET",$apparr['app_secret']);
$h = new Tuan2345Auth($_SESSION['request_token']['oauth_token'],$_SESSION['request_token']['oauth_token_secret']);

$accessToken   = $h->getAccessToken($_REQUEST['oauth_verifier']);
$oauth_token  = $accessToken["oauth_token"];
$oauth_secret = $accessToken["oauth_token_secret"];
$uid = $accessToken["user_id"];
$client = new Tuan2345Client();
$userinfo = $client->get_userinfo($uid);
if($userinfo){
$userinfo_arr = explode("|",$userinfo); 
}

$_SESSION["access_token"] =array('oauth_token'=>$oauth_token,'oauth_token_secret'=>$oauth_secret);

$errorno = $accessToken["error_no"];
if(!empty($errorno))
{
    //可以对应跳转到错误页面
    echo " AUTH FAIL !";
    echo $accessToken["msg"]; 
}
if(!empty($oauth_token) && !empty($oauth_secret))
{
   
	/*echo " AUTH SUCCESS <br>";
    echo "access_token=".$accessToken["oauth_token"]."<br>";
    echo "access_token_secret=".$accessToken["oauth_token_secret"]."<br>";
    echo "qid=".$userinfo_arr[0]."<br>";
    echo "qname=".$userinfo_arr[1]."<br>";
    echo "qmail=".$userinfo_arr[2]."<br>";*/
	$pass = $userinfo_arr[0].$apparr['app_encrypt'];
	if ($userinfo_arr[1] == '')
	{
	 $userinfo_arr[1] = $userinfo_arr[2];
	}
	if ($userinfo_arr[2] == '')
	{
	 $userinfo_arr[2] = $userinfo_arr[1] . "@126.com";
	}
    if ($userinfo_arr[1] != '')
   {   
     if ($user->check_user($userinfo_arr[1]))
    {
      if ($user->login($userinfo_arr[1], $pass))
	  {	
	    $_SESSION['qid'] = $userinfo_arr[0];
	  }
    }
    else
   {
	 if (register($userinfo_arr[1],$pass, $userinfo_arr[2]) !== false)
	 {
		 $_SESSION['qid'] = $userinfo_arr[0];
		 $aliss_name = 'tuan2345'.$userinfo_arr[0];
		 $sql = "UPDATE " .  $ecs->table('users') . " SET aliss_name='$aliss_name' WHERE user_id='$user_id'";
		 $db->query($sql);
	 }
    }
  }
}
$surl = "http://" . $_SERVER['HTTP_HOST'].'/'.$url;
ecs_header("Location: $surl\n");
exit;
//echo '<br><a href="index.html" >再来一次</a>';
?>
