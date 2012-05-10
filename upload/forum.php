<?php

/**
 * ECGROUPON 讨论区
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
if ($_SESSION['user_id'] <= 0)
{
    ecs_header("Location: login.php\n");
    exit;
}
assign_public($city_id);
if ($_POST['act'] == 'add_forum')
{
    $forum['forum_title'] = trim($_POST['forumtitle']);
    $forum['forum_content'] = trim($_POST['forumcontent']);
    if (empty($forum['forum_title']))
	{
	   show_group_message('请输入标题!', '', "forum.php?act=new");
	}
	if (empty($forum['forum_content']))
	{
	   show_group_message('请输入内容!', '', "forum.php?act=new");
	}

    $status = 1 - $GLOBALS['_CFG']['group_comment'];
    $forum['user_id'] = $_SESSION['user_id'];
    $user_name =  $_SESSION['user_name'];
	$type_arr = explode('_',$_POST['type_id']);
    $forum['user_name'] = htmlspecialchars($user_name);
    $forum['forum_type'] = in_array($type_arr[2],array(0,1,2)) ? $type_arr[2] : 0;
	$forum['cid'] = intval($type_arr[1]);
	$forum['city_id'] = intval($type_arr[0]);
	$forum['ip_address'] = real_ip();
	$forum['forum_status'] = $status;
	$forum['add_time'] = gmtime();
	$res = $db->autoExecute($ecs->table('group_forum'), $forum, 'INSERT');
    if ($res)
    {
        show_group_message('话题发表成功,请等待管理员审核!', '', "forum.php");
    }

}
elseif ($_GET['act'] == 'new')
{
  $smarty->assign('act', 'new');
  $smarty->assign('type', $_GET['type']);
  $smarty->assign('class_list', group_class_list(3,true,'forum.php'));
  $smarty->display('group_forum.dwt');
}
else
{
    
	$page = intval($_GET['page']);
	$page = $page > 0 ? $page : 1;
	$type = trim($_GET['type']);
	$type = in_array($type, array('public','all','city','report','transferbuy')) ? $type : 'all';
	$cache_id = sprintf('%X', crc32($page.'-'. $type. '-' . $city_id));
    /* 如果没有缓存，生成缓存 */
    if (!$smarty->is_cached('group_forum.dwt', $cache_id))
    {

	  $size = 15;
	  if ($type == 'public')
	  {
	     $city_sql = 'city_id=0 AND forum_type=0';
	  }
	  elseif ($type == 'city')
	  {
		 $city_sql = "city_id='$city_id' AND forum_type=0";
	  }
	   elseif ($type == 'transferbuy')
	  {
		 $city_sql = "city_id='$city_id' AND forum_type in(1,2)";
	  }
	  else
	  {
	  	 $city_sql = 'forum_type=0';
	  }
	  $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('group_forum').
          " WHERE  $city_sql AND forum_status = 1 AND parent_id = 0");
      $sql = 'SELECT gf.*,gc.class_name FROM ' . $GLOBALS['ecs']->table('group_forum') .
	        " AS gf LEFT JOIN " . $GLOBALS['ecs']->table('group_class') . " AS gc ON gc.cid=gf.cid".
            " WHERE $city_sql AND gf.forum_status = 1 AND gf.parent_id = 0 ".
            ' ORDER BY gf.forum_id DESC';
      $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);
      $arr = array();
      $ids = '';
      while ($row = $GLOBALS['db']->fetchRow($res))
      {
          $arr[$row['forum_id']]['id']       = $row['forum_id'];
          $arr[$row['forum_id']]['username'] = $row['user_name'];
		  $arr[$row['forum_id']]['forumtitle'] = $row['forum_title'];
		  $arr[$row['forum_id']]['class_name'] = $row['class_name'];
          $arr[$row['forum_id']]['forumcontent']  = str_replace('\r\n', '<br />', htmlspecialchars($row['forum_content']));
          $arr[$row['forum_id']]['forumcontent']  = str_replace('\n', '<br />', $arr[$row['forum_id']]['forumcontent']);
          $arr[$row['forum_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
	      $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('group_forum') . " WHERE parent_id = '$row[forum_id]'";
          $arr[$row['forum_id']]['replay_num']  = $GLOBALS['db']->getOne($sql);
		  $sql = 'SELECT forum_id,forum_content,add_time FROM ' . $GLOBALS['ecs']->table('group_forum') .
		         " WHERE parent_id = '$row[forum_id]' ORDER BY forum_id DESC LIMIT 1";
		  $replay_arr =  $GLOBALS['db']->getRow($sql);
		  $replay_arr['add_time'] = local_date('Y-m-d H:i:s', $replay_arr['add_time']); 		 
          $arr[$row['forum_id']]['replay'] = $replay_arr;
		  $arr[$row['forum_id']]['click_num'] = $row['click_num'];
		  $arr[$row['forum_id']]['forum_url'] = rewrite_groupurl('thread.php',array('fid' => $row['forum_id']));
      }
	  $smarty->assign('forumlist', $arr);
	  $pager  = get_group_pager('forum.php',array('type'=>$type), $count, $page, $size);
 	  $smarty->assign('pager', $pager);
	  $smarty->assign('type', $type);
	  $smarty->assign('where', 'forum');
	  $smarty->assign('act', 'list');
   }
    $smarty->display('group_forum.dwt',$cache_id);
}
?>