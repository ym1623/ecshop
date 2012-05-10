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
$apparr = get_loginconfig('goutuan');
$indexurl = array('team.php','index.php');
$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);

if (empty($apparr))
{
  echo '请先开通购团导航的一站通登录,获取APP_KEY与APP_SECRET!';
  echo '<br>','<a href='."http://".$_SERVER['HTTP_HOST'].'/'.$url.'>返回首页';
  exit;
}
define("APP_KEY",$apparr['app_key']);
define("APP_SECRET",$apparr['app_secret']);

require(ROOT_PATH . "oauth/goutuan/lib/Client.php");
$client = new Client();
$auth_code = $_GET['code'];
$uid       = intval($_GET['uid']);
// grant_type == authorization_code.
if (!$session && $auth_code) {
        $access_token = $client->getAccessTokenFromAuthorizationCode($auth_code,$uid);
        $session = $client->getSessionObject($access_token);
        $session = $client->validateSessionObject($session);
}
$me = NULL;
// Session based API call.
if ($session) {
  try {
  	$session['oauth_token'] = $session['access_token'];
    $me = $client->post(APP_RESOURCE_URI,$session);
 
   $userinfo=  json_decode($me,true);
   
if ( !empty($userinfo)) {
	
	$accessToken = array();
	$accessToken["qname"] = strval($userinfo['user_name']);
	$accessToken["qmail"] = strval($userinfo['email']);
	//$u['city_id'] = abs(intval($userinfo['city_id']));
	//$u['mobile'] = strval($userinfo['mobile']);
	//$u['enable'] = 'N';	
	$accessToken["qid"] = intval($userinfo['id']). $apparr['app_encrypt'];
	
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
	  }
    }
    else
   {
	 if (register($accessToken["qname"], $accessToken["qid"], $accessToken["qmail"]) !== false)
	 {
		 $aliss_name = 'goutuan'.$accessToken["qid"];
		 $sql = "UPDATE " .  $ecs->table('users') . " SET aliss_name='$aliss_name' WHERE user_id='$user_id'";
		 $db->query($sql);

	 }
    }
   }


  }
  catch (Exception $e) {
    error_log($e);
  }
}
$surl = "http://" . $_SERVER['HTTP_HOST'].'/'.$url;
ecs_header("Location: $surl\n");
exit;

?>
