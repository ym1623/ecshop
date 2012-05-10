<?php
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
$authorizeUrl = $client->authorization_uri.'?client_id='.$client->key.'&response_type=code&'.'redirect_uri='.APP_CALLBACK_URI;
ecs_header("Location:$authorizeUrl");
?>