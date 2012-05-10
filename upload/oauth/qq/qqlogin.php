<?php
/**
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require('./init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$apparr = get_loginconfig('qq');
$indexurl = array('team.php','index.php');
$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);
if (empty($apparr))
{
  echo '请先开通QQ的一站通登录,获取APP_KEY与APP_ID!';
  echo '<br>','<a href='."http://".$_SERVER['HTTP_HOST'].'/'.$url.'>返回首页';
  exit;
}

require_once(ROOT_PATH . "oauth/qq/comm/utils.php"); 
require_once("get_request_token.php");
$_SESSION["appid"]    = $apparr['app_secret']; //opensns.qq.com 申请到的appid
$_SESSION["appkey"]   = $apparr['app_key']; //opensns.qq.com 申请到的appkey

/**
 * @brief redirect to QQ login page
 *        rfc1738 urlencode
 * @param $appid
 * @param $appkey
 * @param $callback
 */
function redirect_to_login($appid, $appkey, $callback)
{
    //授权登录页
    $redirect = "http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize?oauth_consumer_key=$appid&";

    //获取request token
    $result = array();
    $request_token = get_request_token($appid, $appkey);
    parse_str($request_token, $result);

    //request token, request token secret 需要保存起来
    //在demo演示中，直接保存在全局变量中.真实情况需要网站自己处理
    $_SESSION["token"]        = $result["oauth_token"];
    $_SESSION["secret"]       = $result["oauth_token_secret"];

    if ($result["oauth_token"] == "")
    {
        //demo中不对错误情况做处理
        //网站需要自己处理错误情况
        exit;
    }

    //302跳转到授权页面
    $redirect .= "oauth_token=".$result["oauth_token"]."&oauth_callback=".rawurlencode($callback);
    header("Location:$redirect");
}

//tips
//when a user clicks the QQ login button, you should call *this* to redirect to QQ login page.
if ($_SESSION['appid'] != '' && $_SESSION["appkey"] != '')
{
  redirect_to_login($_SESSION["appid"], $_SESSION["appkey"], $_SESSION["callback"]);
}
?>
