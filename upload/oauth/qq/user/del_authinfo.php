<?php
/*
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */
require_once("../comm/utils.php");

 /*
 * @brief delete auth info 
 * @param $appid
 * @param $appkey
 * @param $access_token
 * @param $access_token_secret
 * @param $openid
 *
 */
function del_authinfo($appid, $appkey, $access_token, $access_token_secret, $openid)
{
    //delete auth info 的api接口，不要随便更改!!
    $url    = "http://openapi.qzone.qq.com/user/del_authinfo";
    echo do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid);
}

//test
del_authinfo($_SESSION["appid"], $_SESSION["appkey"], $_SESSION["token"], $_SESSION["secret"], $_SESSION["openid"]);
?>
