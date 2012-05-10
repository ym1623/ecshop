<?php
/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$user_id = intval($_SESSION['user_id']);

if ($user_id <= '0')

{

	$indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$_CFG['groupindex']]);
     ecs_header("Location: $url\n");
     exit; 
}

    $page = intval($_GET['page']);
	$page = $page > 0 ? $page : 1;
	$cache_id = sprintf('%X', crc32($page.'-'. $user_id));
    $city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
    $size = 15;
    $type = 2;
	$count = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
           " WHERE  comment_type = '$type' AND parent_id = 0 AND user_id ='$user_id' AND status = 1");
    $sql = 'SELECT c.*,ga.group_id,ga.group_name,ga.is_finished,ga.end_time  FROM ' . $GLOBALS['ecs']->table('comment') . " AS c," . 
	         $GLOBALS['ecs']->table('group_activity') . " AS ga".
            " WHERE c.comment_type = '$type' AND c.parent_id = 0 AND c.id_value=ga.group_id AND c.user_id ='$user_id' AND c.status = 1".
            ' ORDER BY c.comment_id DESC';
      $res = $GLOBALS['db']->selectLimit($sql, $size, ($page-1) * $size);
      $smarty->assign('group_city', get_group_city());
      $arr = array();
      $ids = '';
      while ($row = $GLOBALS['db']->fetchRow($res))
      {
          $ids .= $ids ? ",$row[comment_id]" : $row['comment_id'];
          $arr[$row['comment_id']]['group_name'] = $row['group_name'];
		  $arr[$row['comment_id']]['is_finished'] = $row['is_finished'];
		  $arr[$row['comment_id']]['end_time'] = local_date('Y-m-d', $row['end_time']);
          $arr[$row['comment_id']]['group_id']     = $row['group_id'];
          $arr[$row['comment_id']]['email']    = $row['email'];
          $arr[$row['comment_id']]['username'] = $row['user_name'];
          $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
          $arr[$row['comment_id']]['content']  = str_replace('\n', '<br />', $arr[$row['comment_id']]['content']);
          $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
          $arr[$row['comment_id']]['add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
		  $arr[$row['comment_id']]['group_url']  = rewrite_groupurl('team.php',array('id' => $row['group_id']));
		  
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
            $arr[$row['parent_id']]['re_add_time'] = local_date('Y-m-d H:i:s', $row['add_time']);
            $arr[$row['parent_id']]['re_email']    = $row['email'];
            $arr[$row['parent_id']]['re_username'] = $row['user_name'];
        }
      }
	  $smarty->assign('comments', $arr);
	  $pager  = get_pager('myask.php',array(), $count, $page, $size);
	  $smarty->assign('pager', $pager);
	  $smarty->assign('menu',  'myask');
      assign_public($city_id);
    $smarty->display('group_myask.dwt');


?>