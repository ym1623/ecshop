<?php
/*
 * 获取requestToken,并且跳转到用户认证页面
 */
session_start(); 
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require('./init.php');
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

//在此修改相应的回调地址
$callback = "http://".$_SERVER['HTTP_HOST'].'/oauth/tuan800/callback.php';

$authSvc      = new TuanAuth();
$token        = $authSvc->getRequestToken();
//var_dump($token);
//输出获取到的Token和Secret
/*foreach($token as $key=>$value)
{
	echo $key."=>".$value;
	echo "&nbsp;&nbsp;&nbsp;&nbsp;";
}*/


if($token["oauth_token"])
{
    $_SESSION['request_token'] = $token;
    
    //输出相应的Token数据
    $authorizeUrl = $authSvc->getAuthorizeURL( $token['oauth_token'] , $callback );
    header("Location:$authorizeUrl");
}
else
{
    //错误了,可以定义跳转到对应错误页面
    echo " AUTH FAIL ! \r\n";
    echo $token["msg"];
}

?>