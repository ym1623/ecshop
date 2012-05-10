<?php

/**
 * ECGROPON 讨论区管理程序
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}
$exc = new exchange($ecs->table("group_forum"), $db, 'forum_id', 'forum_status');

if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    admin_priv('forum_priv');

    $smarty->assign('ur_here',      $_LANG['05_forum_manage']);
    $smarty->assign('full_page',    1);

    $list = get_forum_list();

    $smarty->assign('forum_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $smarty->assign('city_list',    get_group_city());
    $smarty->assign('cat_list',     group_class_list(3));

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('forum_list.htm');
}
elseif ($_REQUEST['act'] == 'query')
{
    $list = get_forum_list();

    $smarty->assign('forum_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('forum_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
elseif ($_REQUEST['act'] == 'replay_query')
{
    $list = get_replay_list();

    $smarty->assign('forum_list', $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('forum_info.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

elseif ($_REQUEST['act']=='reply')
{
    /* 检查权限 */
    admin_priv('forum_priv');

    $forum_info = array();
    $reply_info   = array();
    $id_value     = array();

    //$sql = "SELECT * FROM " .$ecs->table('group_forum'). " WHERE forum_id = '$_REQUEST[id]'";
	$sql  = "SELECT gf.*,gc.city_name,gcl.class_name FROM " .$GLOBALS['ecs']->table('group_forum').
	        ' AS gf LEFT JOIN ' . $GLOBALS['ecs']->table('group_city') . ' AS gc ON gc.city_id = gf.city_id '.
		    'LEFT JOIN ' . $GLOBALS['ecs']->table('group_class') . ' AS gcl ON gcl.cid = gf.cid '.
	        " WHERE forum_id = '$_REQUEST[id]'";
    $forum_info = $db->getRow($sql);
    $forum_info['content']  = str_replace('\r\n', '<br />', htmlspecialchars($forum_info['content']));
    $forum_info['content']  = nl2br(str_replace('\n', '<br />', $forum_info['content']));
    $forum_info['add_time'] = local_date($_CFG['time_format'], $forum_info['add_time']);
    $_REQUEST['parent_id'] = $_REQUEST['id'];
    $list = get_replay_list();
    /* 模板赋值 */
	$forum_info['replay_num'] = count($list['item']);
	$smarty->assign('forum_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    $smarty->assign('full_page',    1);
    $smarty->assign('forum_info',    $forum_info); 
    $smarty->assign('ur_here',      $_LANG['forum_manage']);
    $smarty->assign('action_link',  array('text' => $_LANG['forum_manage'],'href' => 'forum_manage.php?act=list'));

    /* 页面显示 */
    assign_query_info();
    $smarty->display('forum_info.htm');
}
elseif ($_REQUEST['act'] == 'check')
{
    if ($_REQUEST['check'] == 'allow')
    {
        $sql = "UPDATE " .$ecs->table('group_forum'). " SET forum_status = 1 WHERE forum_id = '$_REQUEST[id]'";
        $db->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        ecs_header("Location: forum_manage.php?act=reply&id=$_REQUEST[id]\n");
        exit;
    }
    else
    {
        $sql = "UPDATE " .$ecs->table('group_forum'). " SET forum_status = 0 WHERE forum_id = '$_REQUEST[id]'";
        $db->query($sql);

        clear_cache_files();

        ecs_header("Location: forum_manage.php?act=reply&id=$_REQUEST[id]\n");
        exit;
    }
}

elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('comment_priv');

    $id = intval($_GET['id']);

    $sql = "DELETE FROM " .$ecs->table('group_forum'). " WHERE forum_id = '$id'";
    $res = $db->query($sql);
    if ($res)
    {
        $db->query("DELETE FROM " .$ecs->table('group_forum'). " WHERE parent_id = '$id'");
    }

    admin_log('', 'remove', 'ads');

    $url = 'forum_manage.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
elseif ($_REQUEST['act'] == 'remove_replay')
{
    check_authz_json('comment_priv');

    $id = intval($_GET['id']);

    $sql = "DELETE FROM " .$ecs->table('group_forum'). " WHERE forum_id = '$id'";
    $res = $db->query($sql);
    admin_log('', 'remove', 'ads');

    $url = 'forum_manage.php?act=replay_query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'set_show')
{
    check_authz_json('add_city');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("forum_status='$val'", $id);

    make_json_result($val);
}
/*------------------------------------------------------ */
//-- 修改商品排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('goods_manage');

    $forum_id       = intval($_POST['id']);
    $sort_order     = intval($_POST['val']);

    if ($exc->edit("forum_sort= '$sort_order'", $forum_id))
    {
        clear_cache_files();
        make_json_result($sort_order);
    }
}
elseif ($_REQUEST['act'] == 'batch')
{
    admin_priv('comment_priv');
    $action = isset($_POST['sel_action']) ? trim($_POST['sel_action']) : 'deny';
    if (isset($_POST['checkboxes']))
    {
        switch ($action)
        {
            case 'remove':
                $db->query("DELETE FROM " . $ecs->table('group_forum') . " WHERE " . db_create_in($_POST['checkboxes'], 'forum_id'));
                $db->query("DELETE FROM " . $ecs->table('group_forum') . " WHERE " . db_create_in($_POST['checkboxes'], 'parent_id'));
                break;

           case 'allow' :
              
			$db->query("UPDATE " . $ecs->table('group_forum') . " SET forum_status = 1  WHERE " . db_create_in($_POST['checkboxes'], 'forum_id'));
               break;

           case 'deny' :
            $db->query("UPDATE " . $ecs->table('group_forum') . " SET forum_status = 0  WHERE " . db_create_in($_POST['checkboxes'], 'forum_id'));
               break;

           default :
               break;
        }

        clear_cache_files();
        $action = ($action == 'remove') ? 'remove' : 'edit';
        admin_log('', $action, 'adminlog');

        $link[] = array('text' => $_LANG['back_list'], 'href' => 'forum_manage.php?act=list');
        sys_msg(sprintf($_LANG['batch_drop_success'], count($_POST['checkboxes'])), 0, $link);
    }
    else
    {
        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'forum_manage.php?act=list');
        sys_msg($_LANG['no_select_comment'], 0, $link);
    }
}

/**
 * 获取话题列表
 * @access  public
 * @return  array
 */
function get_forum_list()
{
    /* 查询条件 */
    $filter['keywords']     = empty($_REQUEST['keywords']) ? 0 : trim($_REQUEST['keywords']);
    if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
    {
        $filter['keywords'] = json_str_iconv($filter['keywords']);
    }
    $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['parent_id']    = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : '0';
    $filter['cid']          = isset($_REQUEST['cid']) && intval($_REQUEST['cid']) >= 0 ? intval($_REQUEST['cid']) : '-1';
	$filter['city_id']      = isset($_REQUEST['city_id']) && intval($_REQUEST['city_id']) >= 0 ? intval($_REQUEST['city_id']) : '-1';
    $filter['forum_type']   = isset($_REQUEST['forum_type']) && intval($_REQUEST['forum_type']) >= 0 ? intval($_REQUEST['forum_type']) : '0';

	$where = " WHERE gf.parent_id = '0' AND forum_type = '$filter[forum_type]'";
	if ($filter['cid'] >= 0)
	{
	  if ($filter['cid'] > 0)
	  {
	    $where .= " AND gf.cid='$filter[cid]'";
	  }
	  else
	  {
	   	$where .= " AND gf.cid > '0'";
	  }
	}
	if ($filter['city_id'] >= 0)
	{
	    if ($filter['city_id'] > 0)
	  {
	    $where .= " AND gf.city_id='$filter[city_id]'";
	  }
	  else
	  {
	   	$where .= " AND gf.city_id > '0'";
	  }
	}
	if ($filter['keywords'] != '')
	{
	   $where .= " AND gf.forum_title LIKE '%" . mysql_like_quote($filter['keywords']) . "%' " ;
	}
    $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('group_forum'). " AS gf " . $where ;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 获取话题数据 */
    $arr = array();
    $sql  = "SELECT gf.*,gc.city_name,gcl.class_name FROM " .$GLOBALS['ecs']->table('group_forum').
	        ' AS gf LEFT JOIN ' . $GLOBALS['ecs']->table('group_city') . ' AS gc ON gc.city_id = gf.city_id '.
		    'LEFT JOIN ' . $GLOBALS['ecs']->table('group_class') . ' AS gcl ON gcl.cid = gf.cid '.
	        $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT ". $filter['start'] .", $filter[page_size]";
    $res  = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
	    if ($filter['parent_id'] == 0)
		{
	      $sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('group_forum') . " WHERE parent_id = '$row[forum_id]'";
          $row['replay_num']  = $GLOBALS['db']->getOne($sql);
		}
        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

        $arr[] = $row;
    }
    $filter['keywords'] = stripslashes($filter['keywords']);
    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
function get_replay_list()
{
    /* 查询条件 */
    $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
    $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$filter['parent_id']    = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : '0';
	$where = " WHERE parent_id = '$filter[parent_id]'";
    $sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('group_forum'). $where ;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    /* 获取话题数据 */
    $arr = array();
    $sql  = "SELECT * FROM " .$GLOBALS['ecs']->table('group_forum'). $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT ". $filter['start'] .", $filter[page_size]";
    $res  = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);

        $arr[] = $row;
    }
    $filter['keywords'] = stripslashes($filter['keywords']);
    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>