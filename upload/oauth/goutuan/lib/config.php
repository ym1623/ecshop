<?php

#回调地址
define('APP_CALLBACK_URI','http://www.'.$_SERVER['HTTP_HOST'].'.com/client/callback.php');

#获取Authorization Code 的URI
define("APP_AUTHORIZATION_URI","http://oauth.goutuan.net/authorize.php");

#获取Access Token 的URI
define("APP_ACCESS_TOKEN_URI","http://oauth.goutuan.net/token.php");

#API Order URI
define("APP_ORDER_URI","http://oauth.goutuan.net/order.php");

#资源信息
define('APP_RESOURCE_URI','http://oauth.goutuan.net/protected_resource.php');
?>