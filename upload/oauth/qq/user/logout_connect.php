<?php
/*
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */
require_once("../comm/utils.php");

 /*
 * @brief logout qzone platform 
 * @param $appid
 * @param $appkey
 * @param $access_token
 * @param $access_token_secret
 * @param $openid
 *
 */
function logout_connect($appid, $appkey, $access_token, $access_token_secret, $openid)
{
    //log out qzone 的api接口，不要随便更改!!
    $url    = "http://openapi.qzone.qq.com/user/logout_connect";
    echo do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid);
}

//test
logout_connect($_SESSION["appid"], $_SESSION["appkey"], $_SESSION["token"], $_SESSION["secret"], $_SESSION["openid"]);
?>
