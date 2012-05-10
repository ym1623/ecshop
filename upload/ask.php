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
assign_public($city_id);
/* 取得评论列表 */
if ($_POST['act'] == 'add_ask')
{
	       /* 评论是否需要审核 */
	 if ($_SESSION['user_id'] <= 0)
    {
        show_group_message($_LANG['gb_message_fail'], '', 'login.php', 'error');
    }
    $status = 1 - $GLOBALS['_CFG']['group_comment'];
	$comment = array();
    $comment['user_id'] = $_SESSION['user_id'];
    $email =  $_SESSION['email'] ;
    $user_name =  $_SESSION['user_name'];
    $comment['email'] = htmlspecialchars($email);
    $comment['user_name'] = htmlspecialchars($user_name);
    $comment['content'] = trim($_POST['msg_content']);
	$comment['id_value'] = intval($_POST['id']);
	$comment['comment_type'] = 2;
	$comment['comment_rank'] = 0;
	$comment['add_time'] = gmtime();
	$comment['id_address'] = real_ip();
	$comment['status'] = $status;
	$comment['parent_id'] = 0;
    /* 保存评论内容 */
	$id = intval($_POST['id']);
	if (!empty($comment))
	{
      $res = $db->autoExecute($ecs->table('comment'), $comment, 'INSERT');
      if ($res)
      {
        show_group_message('留言成功!', '', "ask.php?gid=$id", 'error');
      }
	}
    else
	{
	   show_group_message('信息不完整!', '', "ask.php?gid=$id", 'error');
	}
}
else
{
	$page = intval($_GET['page']);
	$group_id = intval($_GET['gid']);
	$page = $page > 0 ? $page : 1;
	$is_line = intval($_SESSION['user_id']) > 0 ? 1 : 0; 
	$cache_id = sprintf('%X', crc32($page.'-'. $is_line . '-' . $group_id));
    /* 如果没有缓存，生成缓存 */
    if (!$smarty->is_cached('group_ask.dwt', $cache_id))
    {

	  $size = 15;
      $type = 2;
	  $othersql = '';
	  if ($group_id > 0)
	  {
	    $othersql = " AND id_value='$group_id'";
	  }
	  $count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
          " WHERE  comment_type = '$type' AND status = 1 AND parent_id = 0" . $othersql);
      $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
            " WHERE comment_type = '$type' AND status = 1 AND parent_id = 0 ".$othersql.
            ' ORDER BY comment_id DESC';
      $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);
      $smarty->assign('group_city', get_group_city());
      $arr = array();
      $ids = '';
      while ($row = $GLOBALS['db']->fetchRow($res))
      {
          $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
          $arr[$row['comment_id']]['id']       = $row['comment_id'];
          $arr[$row['comment_id']]['email']    = $row['email'];
          $arr[$row['comment_id']]['username'] = $row['user_name'];
          $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
          $arr[$row['comment_id']]['content']  = str_replace('\n', '<br />', $arr[$row['comment_id']]['content']);
          $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
          $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
      }
      /* 取得已有回复的评论 */
      if ($ids)
      {
        $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
                " WHERE parent_id IN( $ids )";
        $res = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetch_array($res))
        {
            $arr[$row['parent_id']]['re_content']  = str_replace('\n', '<br />', htmlspecialchars($row['content']));
            $arr[$row['parent_id']]['re_add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
            $arr[$row['parent_id']]['re_email']    = $row['email'];
            $arr[$row['parent_id']]['re_username'] = $row['user_name'];
        }
      }
	  $smarty->assign('comments', $arr);
	  $pager  = get_group_pager('ask.php',array('gid'=>$group_id), $count, $page, $size);
      $group_buy = get_group_buy_info($group_id);
      $smarty->assign('group_buy', $group_buy);
	  $smarty->assign('today_group',  get_today_grouplist($group_id,$group_buy['city_id']));
	  $smarty->assign('pager', $pager);
	  $smarty->assign('invite_url', rewrite_groupurl('invite.php',array('gid'=>$group_buy['group_id'])));
	  $smarty->assign('uid', intval($_SESSION['user_id']));
   }
    $smarty->display('group_ask.dwt',$cache_id);
}
?>