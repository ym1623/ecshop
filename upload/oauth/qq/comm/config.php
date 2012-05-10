<?php
/*
 * @brief a configure file 
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */

//本文件作为demo的一个配置文件
//用于存储appid，appkey，token等信息
//实际应用中，你应该使用自己的存储保存这些信息

ini_set("error_reporting", E_ALL);
ini_set("display_errors", TRUE);
session_id("demo");
session_start();

//请将下面信息更改成自己申请的信息
//$_SESSION["appid"]    = 200018; //opensns.qq.com 申请到的appid
//$_SESSION["appkey"]   = "954cd06e83fd4ebc9b5a08270ef0920e"; //opensns.qq.com 申请到的appkey
$_SESSION["callback"] = "http://".$_SERVER['HTTP_HOST']."/oauth/qq/get_access_token.php"; //QQ登录成功后跳转的地址

?>
