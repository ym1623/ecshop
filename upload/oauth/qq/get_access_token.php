<?php
/*
 * This is only a simple demo.
 * It is a free software; you can redistribute it 
 * and/or modify it. 
 */
define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
require('./init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(ROOT_PATH . 'includes/cls_json.php');
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
$_SESSION["appid"]    = $apparr['app_secret']; //opensns.qq.com 申请到的appid
$_SESSION["appkey"]   = $apparr['app_key']; //opensns.qq.com 申请到的appkey

/**
 * @brief get a access token 
 *        rfc1738 urlencode
 * @param $appid
 * @param $appkey
 * @param $request_token
 * @param $request_token_secret
 * @param $vericode
 *
 * @return a string, as follows:
 *      oauth_token=xxx&oauth_token_secret=xxx&openid=xxx&oauth_signature=xxx&oauth_vericode=xxx&timestamp=xxx
 */
function get_access_token($appid, $appkey, $request_token, $request_token_secret, $vericode)
{
    //获取access token接口，不要随便更改!!
    $url    = "http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token?";
    //构造签名串.源串:方法[GET|POST]&uri&参数按照字母升序排列
    $sigstr = "GET"."&".rawurlencode("http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token")."&";

    //必要参数，不要随便更改!!
    $params = array();
    $params["oauth_version"]          = "1.0";
    $params["oauth_signature_method"] = "HMAC-SHA1";
    $params["oauth_timestamp"]        = time();
    $params["oauth_nonce"]            = mt_rand();
    $params["oauth_consumer_key"]     = $appid;
    $params["oauth_token"]            = $request_token;
    $params["oauth_vericode"]         = $vericode;

    //对参数按照字母升序做序列化
    $normalized_str = get_normalized_string($params);
    $sigstr        .= rawurlencode($normalized_str);

    //echo "sigstr = $sigstr";

    //签名,确保php版本支持hash_hmac函数
    $key = $appkey."&".$request_token_secret;
    $signature = get_signature($sigstr, $key);
    //构造请求url
    $url      .= $normalized_str."&"."oauth_signature=".rawurlencode($signature);

    return file_get_contents($url);
}
function get_user_info($appid, $appkey, $access_token, $access_token_secret, $openid)
{
    //get user info 的api接口，不要随便更改!!
    $url    = "http://openapi.qzone.qq.com/user/get_user_info";
    return do_get($url, $appid, $appkey, $access_token, $access_token_secret, $openid);
}

//tips//
/**
 * QQ互联登录，授权成功后会回调此地址
 * 必须要用授权的request token换取access token
 * 访问QQ互联的任何资源都需要access token
 * 目前access token是长期有效的，除非用户解除与第三方绑定
 * 如果第三方发现access token失效，请引导用户重新登录QQ互联，授权，获取access token
 */
//print_r($_REQUEST);

//授权成功后，会返回用户的openid
//检查返回的openid是否是合法id
if (!is_valid_openid($_REQUEST["openid"], $_REQUEST["timestamp"], $_REQUEST["oauth_signature"]))
{
    //demo对错误简单处理
    echo "###invalid openid\n";
    echo "sig:".$_REQUEST["oauth_signature"]."\n";
    exit;
}

//tips
//这里已经获取到了openid，可以处理第三方账户与openid的绑定逻辑
//但是我们建议第三方等到获取accesstoken之后在做绑定逻辑

//用授权的request token换取access token
$access_str = get_access_token($_SESSION["appid"], $_SESSION["appkey"], $_REQUEST["oauth_token"], $_SESSION["secret"], $_REQUEST["oauth_vericode"]);
//echo "access_str:$access_str\n";
$result = array();
parse_str($access_str, $result);

//print_r($result);

//error
if (isset($result["error_code"]))
{
    echo "error_code = ".$result["error_code"];
    exit;
}

//获取access token成功后也会返回用户的openid
//我们强烈建议第三方使用此openid
//检查返回的openid是否是合法id
if (!is_valid_openid($result["openid"], $result["timestamp"], $result["oauth_signature"]))
{
    //demo对错误简单处理
    echo "@@@invalid openid";
    echo "sig:".$result["oauth_signature"]."\n";
    exit;
}

//将access token，openid保存!!
//XXX 作为demo,临时存放在session中，网站应该用自己安全的存储系统来存储这些信息
//$_SESSION["token"]   = $result["oauth_token"];
//$_SESSION["secret"]  = $result["oauth_token_secret"]; 
//$_SESSION["openid"]  = $result["openid"];
//print_r($_SESSION);
//第三方处理用户绑定逻辑
//将openid与第三方的帐号做关联
//bind_to_openid();

//test
$qquser = get_user_info($_SESSION["appid"], $_SESSION["appkey"],  $result["oauth_token"], $result["oauth_token_secret"],$result["openid"]);
$json  = new JSON;
$qquser = $json->decode($qquser);
if(!empty($result["openid"]) && !empty($qquser->nickname))
{
	 $username = $qquser->nickname;
	 $alissname = $result["openid"];
	 
    if ($alissname != '')
   { 
     	$sql = 'SELECT user_name,user_id,password,email FROM '. $GLOBALS['ecs']->table('users') . "  WHERE aliss_name = '$alissname'";
        $users=$db->getRow($sql);
		if ($users['user_id'] > 0)
		{
		   if ($users['user_name'] != $username)
		   {
			 $sql = 'SELECT user_id FROM '. $GLOBALS['ecs']->table('users') . "  WHERE user_name = '$username'";
             if (!$db->getOne($sql))
			 {
		      $sql = "UPDATE " .  $ecs->table('users') . " SET user_name='$username' WHERE user_id='$users[user_id]'";
		       $db->query($sql);
			   $_SESSION['user_name'] = $username;
			 }
		   }
		   else
		   {
		     $_SESSION['user_name'] = $users['user_name'];
		   }
		   $_SESSION['user_id']   = $users['user_id'];
           $_SESSION['email']     = $users['email'];
		}
       else
      {
		 $password = $user->compile_password(array('password'=>$aliss_name));
		 $sql = 'INSERT INTO '.  $GLOBALS['ecs']->table('users') .
		       "(password,aliss_name)VALUES('$password','$alissname')";
			   
		 $db->query($sql);
		 $user_id = $_SESSION['user_id']=$db->insert_id();
		
		 $sql = 'SELECT user_id FROM '. $GLOBALS['ecs']->table('users') . "  WHERE user_name = '$username'";
         if ($db->getOne($sql) > 0)
		 {
		   $username = $username.$_SESSION['user_id']; 	 
		 }
		 $sql = "UPDATE " .  $ecs->table('users') . " SET user_name='$username' WHERE user_id='$user_id'";
		 $db->query($sql);
		 $_SESSION['user_name'] = $username;
	  }
  }
}
$surl = "http://" . $_SERVER['HTTP_HOST'].'/'.$url;
ecs_header("Location: $surl\n");
?>
