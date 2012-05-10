<?php

/**
 * ECGROUPON 一站通详情页面登录
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/
function ecglogin($from = '')
{
   $sign = 1;
   $top_sign = 2;	
   if ($from == 'tuan2345')
   {
	   $apparr = get_loginconfig('2345');
       $appkey = $apparr['app_key'];
       $appsecret = $apparr['app_secret'];
	   $qid = $_GET['qid'];
	   $qname = $_GET['qname'];
	   $qmail = $_GET['qmail'];
	   $from = $_GET['from'];
	   $sign = trim($_GET['sign']);
	   $gurl = trim($_GET['gurl']);
	   $top_sign = md5($qid . '|' . $qname . '|' . $qmail . '|' . $from . '|'.$gurl .'|' . $appkey . '|' . $appsecret);
	   $qid = $qid . $apparr['app_encrypt'];
   }
    if ($from == 'tuan800')
    {
	  $apparr = get_loginconfig('tuan800');
      $appkey = $apparr['app_key'];
      $appsecret = $apparr['app_secret'];
	  $qid = $_POST['qid'];
	  $qname = $_POST['qname'];
	  $qmail = $_POST['qmail'];
	  $from = $_POST['from'];
	  $sign = trim($_POST['sign']);
	  $is_login = true;
	  $top_sign = md5($qid . '|' . $qname . '|' . $qmail . '|' . $from . '|' . $appkey . '|' . $appsecret);
      $qid = empty($qid) ? '12313123' : $qid;
	  $email = str_pad(mt_rand(88888888, 99999999), 8, '0', STR_PAD_LEFT).'@126.com';
      $qmail = empty($qmail) ? $email : $qmail;
	}
	if ($sign == $top_sign)
    {
	     include_once(ROOT_PATH.'includes/lib_passport.php');
	     if ($qname == '')
	    {
	      $qname = $qmail;
	    }
        if ($qname != '')
       {    
         if ($user->check_user($qname))
        {
		  if ($from == 'tuan800')
		  {
		    $sql = 'SELECT user_id FROM '. $GLOBALS['ecs']->table('users') . "  WHERE user_name = '$qname'";
            $user_id=$db->getOne($sql);
	        $qid = $user_id . $apparr['app_encrypt'];
		  }	
          if ($user->login($qname, $qid))
	      {	
	      }
        }
         else
        {
	      if (register($qname, $qid, $qmail) !== false)
	      { 
		     if ($from == 'tuan800')
		    { 
			   $pass = $_SESSION['user_id'] . $apparr['app_encrypt'];
		       $pass = $user->compile_password(array('password'=>$pass));
		       $sql = "UPDATE " .  $ecs->table('users') . " SET password='$pass' WHERE user_id='$_SESSION[user_id]'";
		       $db->query($sql);
			}
	      }
        }
       }
	 }
}
?>