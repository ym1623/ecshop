<?php

/**
 * ECGROUPON 提交用户评论
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

if ($_POST['act'] == 'add_topic')
{
    $fid = intval($_POST['fid']);
    $forum['forum_content'] = trim($_POST['forumcontent']);
	$url = rewrite_groupurl('thread.php',array('fid' => $fid));
	if (empty($forum['forum_content']))
	{
	   show_group_message('请输入回复内容!', '', $url);
	}
    $status = 1 - $GLOBALS['_CFG']['group_comment'];
    $forum['user_id'] = $_SESSION['user_id'];
    $user_name =  $_SESSION['user_name'];
    $forum['user_name'] = htmlspecialchars($user_name);
	//$forum['forum_title'] = trim($_POST['forumtitle']);
	$forum['ip_address'] = real_ip();
	$forum['parent_id'] = $fid;
	$forum['forum_status'] = $status;
	$forum['add_time'] = gmtime();
	$res = $db->autoExecute($ecs->table('group_forum'), $forum, 'INSERT');
    if ($res)
    {
        show_group_message('回复成功,请等待管理员审核!', '', $url);
    }

}
else
{
	$page = intval($_GET['page']);
	$fid = intval($_GET['fid']);
	$page = $page > 0 ? $page : 1;
	$cache_id = sprintf('%X', crc32($page.'-' . $fid));
    /* 如果没有缓存，生成缓存 */
    if (!$smarty->is_cached('group_thread.dwt', $cache_id))
    {

	  $size = 5;
	  $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('group_forum') .
            " WHERE forum_status = 1 AND forum_id = '$fid'";
      $forum_arr = $db->getRow($sql);
	  $forum_arr['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $forum_arr['add_time']);
	  if (empty($forum_arr))
	  {
	     ecs_header("Location: forum.php\n");
         exit;
	  }
	  $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('group_forum').
          " WHERE forum_status = 1 AND parent_id = '$fid'");
	  $forum_arr['replay_num'] = $count;	  
      $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('group_forum') .
            " WHERE forum_status = 1 AND parent_id = '$fid' ".
            ' ORDER BY forum_id DESC';
      $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);
      $arr = array();
      while ($row = $GLOBALS['db']->fetchRow($res))
      {
          $arr[$row['forum_id']]['id']       = $row['forum_id'];
          $arr[$row['forum_id']]['username'] = $row['user_name'];
		  $arr[$row['forum_id']]['forumtitle'] = $row['forum_title'];
		  $arr[$row['forum_id']]['class_name'] = $row['class_name'];
          $arr[$row['forum_id']]['forumcontent']  = str_replace('\r\n', '<br />', htmlspecialchars($row['forum_content']));
          $arr[$row['forum_id']]['forumcontent']  = str_replace('\n', '<br />', $arr[$row['forum_id']]['forumcontent']);
          $arr[$row['forum_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
      }
	  $smarty->assign('fid', $fid);
	  $smarty->assign('forum_arr', $forum_arr);
	  $smarty->assign('forumlist', $arr);
	  $pager  = get_group_pager('thread.php',array('fid'=>$fid), $count, $page, $size);
 	  $smarty->assign('pager', $pager);
   }
   $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_forum') . " SET click_num=click_num+1 WHERE forum_id ='$fid'";
   $db->query($sql);
   $smarty->display('group_thread.dwt',$cache_id);
}
?>