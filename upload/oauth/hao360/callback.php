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
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(ROOT_PATH . 'includes/lib_passport.php');
$apparr = get_loginconfig('hao360');
$indexurl = array('team.php','index.php');
$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);

if (empty($apparr))
{
  echo '请先开通360导航的一站通登录,获取APP_KEY与APP_SECRET!';
  echo '<br>','<a href='."http://".$_SERVER['HTTP_HOST'].'/'.$url.'>返回首页';
  exit;
}

define("APP_KEY",$apparr['app_key']);
define("APP_SECRET",$apparr['app_secret']);
include_once(ROOT_PATH .'oauth/hao360/lib/Hao360Auth.php');
$h = new Hao360Auth($_SESSION['request_token']['oauth_token'],$_SESSION['request_token']['oauth_token_secret']);
$accessToken   = $h->getAccessToken($_REQUEST['oauth_verifier']) ;

$oauth_token  = $accessToken["oauth_token"];
$oauth_secret = $accessToken["oauth_token_secret"];
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
    echo "qid=".$accessToken["qid"]."<br>";
    echo "qname=".$accessToken["qname"]."<br>";
    echo "qmail=".$accessToken["qmail"]."<br>";*/
	if ($accessToken["qname"] == '')
	{
	 $accessToken["qname"] = $accessToken["qmail"];
	}
	if ($accessToken["qmail"] == '')
	{
	 $accessToken["qmail"] = $accessToken["qname"] . "@126.com";
	}
    if ($accessToken["qname"] != '')
   {   
     if ($user->check_user($accessToken["qname"]))
    {
      if ($user->login($accessToken["qname"], $accessToken["qid"]))
	  {	
	    $_SESSION['qid'] = $accessToken["qid"];
	  }
    }
    else
   {
	 if (register($accessToken["qname"], $accessToken["qid"], $accessToken["qmail"]) !== false)
	 {
		 $_SESSION['qid'] = $accessToken["qid"];
		 $aliss_name = 'hao360'.$accessToken["qid"];
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
