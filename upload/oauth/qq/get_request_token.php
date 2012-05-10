<?php
/*
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */

 /**
 * @brief get a request token by appid and appkey
 *        rfc1738 urlencode
 * @param $appid
 * @param $appkey
 *
 * @return a string, the format as follow: 
 *      oauth_token=xxx&oauth_token_secret=xxx
 */
function get_request_token($appid, $appkey)
{
    //获取request token接口, 不要随便更改!!
    $url    = "http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token?";
    //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
    $sigstr = "GET"."&".rawurlencode("http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token")."&";

    //必要参数,不要随便更改!!
    $params = array();
    $params["oauth_version"]          = "1.0";
    $params["oauth_signature_method"] = "HMAC-SHA1";
    $params["oauth_timestamp"]        = time();
    $params["oauth_nonce"]            = mt_rand();
    $params["oauth_consumer_key"]     = $appid;

    //对参数按照字母升序做序列化
    $normalized_str = get_normalized_string($params);
    $sigstr        .= rawurlencode($normalized_str);

    //签名,需要确保php版本支持hash_hmac函数
    $key = $appkey."&";
    $signature = get_signature($sigstr, $key);
    //构造请求url
    $url      .= $normalized_str."&"."oauth_signature=".rawurlencode($signature);

    //echo "$sigstr\n";
    //echo "$url\n";

    return file_get_contents($url);
}

//for test
//echo get_request_token($_SESSION["appid"], $_SESSION["appkey"]);
?>
