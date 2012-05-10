<?php

/**
 * ECGROUPON 导航栏管理
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$exc = new exchange($ecs->table('group_navigation'), $db, 'nav_id', 'nav_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 友情链接列表页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 模板赋值 */
	    admin_priv('groupnav');

    $smarty->assign('ur_here',     $_LANG['list_navs']);
    $smarty->assign('action_link', array('text' => $_LANG['add_navs'], 'href' => 'group_navigation.php?act=add'));
     $smarty->assign('full_page',   1);

    /* 获取友情链接数据 */
    $navs_list = get_navs_list();

    $smarty->assign('navs_list',      $navs_list['list']);
    $smarty->assign('filter',          $navs_list['filter']);
    $smarty->assign('record_count',    $navs_list['record_count']);
    $smarty->assign('page_count',      $navs_list['page_count']);

    $sort_flag  = sort_flag($links_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('navs_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    /* 获取友情链接数据 */
    $navs_list = get_navs_list();

    $smarty->assign('navs_list',      $navs_list['list']);
    $smarty->assign('filter',          $navs_list['filter']);
    $smarty->assign('record_count',    $navs_list['record_count']);
    $smarty->assign('page_count',      $navs_list['page_count']);

    $sort_flag  = sort_flag($links_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('navs_list.htm'), '',
        array('filter' => $navs_list['filter'], 'page_count' => $navs_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加新链接页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('groupnav');

    $smarty->assign('ur_here',     $_LANG['add_nav']);
    $smarty->assign('action_link', array('href'=>'group_navigation.php?act=list', 'text' => $_LANG['list_navs']));
    $smarty->assign('action',      'add');
    $smarty->assign('form_act',    'insert');

    assign_query_info();
    $smarty->display('navs_info.htm');
}

/*------------------------------------------------------ */
//-- 处理添加的链接
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert')
{
    /* 变量初始化 */
    $show_order = (!empty($_POST['show_order'])) ? intval($_POST['show_order']) : 0;
    $nav_name  = (!empty($_POST['nav_name']))  ? sub_str(trim($_POST['nav_name']), 250, false) : '';
    $is_show = (!empty($_POST['is_show'])) ? intval($_POST['is_show']) : 0;
	$nav_url  = trim($_POST['nav_url']);
    /* 查看链接名称是否有重复 */
    if ($exc->num("nav_name", $nav_name) == 0)
    {
        /* 插入数据 */
        $sql    = "INSERT INTO ".$ecs->table('group_navigation')." (nav_name, nav_url, is_show, show_order) ".
                  "VALUES ('$nav_name', '$nav_url', '$is_show', '$show_order')";
        $db->query($sql);

        /* 记录管理员操作 */
        admin_log($_POST['nav_name'], 'add', 'groupnavigation');

        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = 'group_navigation.php?act=add';

        $link[1]['text'] = $_LANG['list_navs'];
        $link[1]['href'] = 'group_navigation.php?act=list';

        sys_msg($_LANG['add'] . "&nbsp;" .stripcslashes($_POST['nav_name']) . " " . $_LANG['attradd_succed'],0, $link);

    }
    else
    {
        $link[] = array('text' => $_LANG['go_back'], 'href'=>'javascript:history.back(-1)');
        sys_msg($_LANG['link_name_exist'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 友情链接编辑页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('groupnav');

    /* 取得友情链接数据 */
    $sql = "SELECT * ".
           "FROM " .$ecs->table('group_navigation'). " WHERE nav_id = '".intval($_REQUEST['id'])."'";
    $nav_arr = $db->getRow($sql);

    $nav_arr['nav_name'] = sub_str($nav_arr['nav_name'], 250, false); // 截取字符串为250个字符避免出现非法字符的情况

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['edit_link']);
    $smarty->assign('action_link', array('href'=>'group_navigation.php?act=list&' . list_link_postfix(), 'text' => $_LANG['list_link']));
    $smarty->assign('form_act',    'update');
    $smarty->assign('action',      'edit');

    $smarty->assign('type',        $type);
    $smarty->assign('nav_arr',     $nav_arr);

    assign_query_info();
    $smarty->display('navs_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑链接的处理页面
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update')
{
    /* 变量初始化 */
    $id         = (!empty($_REQUEST['id']))      ? intval($_REQUEST['id'])      : 0;
    $show_order = (!empty($_POST['show_order'])) ? intval($_POST['show_order']) : 0;
    $nav_name  = (!empty($_POST['nav_name']))  ? sub_str(trim($_POST['nav_name']), 250, false) : '';
    $is_show = (!empty($_POST['is_show'])) ? intval($_POST['is_show']) : 0;
	$nav_url  = trim($_POST['nav_url']);

    /* 更新信息 */
    $sql = "UPDATE " .$ecs->table('group_navigation'). " SET ".
            "nav_name = '$nav_name', ".
            "nav_url = '$nav_url',".
			"is_show = '$is_show',".
            "show_order = '$show_order' ".
            "WHERE nav_id = '$id'";

    $db->query($sql);
    /* 记录管理员操作 */
    admin_log($_POST['nav_name'], 'edit', 'groupnavigation');

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $link[0]['text'] = $_LANG['back_list'];
    $link[0]['href'] = 'group_navigation.php?act=list&' . list_link_postfix();

    sys_msg($_LANG['edit'] . "&nbsp;" .stripcslashes($_POST['nav_name']) . "&nbsp;" . $_LANG['attradd_succed'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑链接名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_nav_name')
{
    check_authz_json('groupnav');

    $id        = intval($_POST['id']);
    $nav_name = json_str_iconv(trim($_POST['val']));

    /* 检查链接名称是否重复 */
    if ($exc->num("nav_name", $nav_name, $id) != 0)
    {
        make_json_error(sprintf($_LANG['nav_name_exist'], $nav_name));
    }
    else
    {
        if ($exc->edit("nav_name = '$nav_name'", $id))
        {
            admin_log($nav_name, 'edit', 'groupnavigation');
            clear_cache_files();
            make_json_result(stripslashes($nav_name));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 删除友情链接
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('groupnav');

    $id = intval($_GET['id']);

    $exc->drop($id);
    clear_cache_files();
    admin_log('', 'remove', 'groupnavigation');

    $url = 'group_navigation.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_is_show')
{
    check_authz_json('groupnav');

    $id    = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_show='$val'", $id);

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 编辑排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_show_order')
{
    check_authz_json('groupnav');

    $id    = intval($_POST['id']);
    $order = json_str_iconv(trim($_POST['val']));

    /* 检查输入的值是否合法 */
    if (!preg_match("/^[0-9]+$/", $order))
    {
        make_json_error(sprintf($_LANG['enter_int'], $order));
    }
    else
    {
        if ($exc->edit("show_order = '$order'", $id))
        {
            clear_cache_files();
            make_json_result(stripslashes($order));
        }
    }
}

/* 获取友情链接数据列表 */
function get_navs_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'nav_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        /* 获得总记录数据 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('group_navigation');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取数据 */
        $sql  = 'SELECT * '.
               ' FROM ' .$GLOBALS['ecs']->table('group_navigation').
                " ORDER by $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $list = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        $list[] = $rows;
    }

    return array('list' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>