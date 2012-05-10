<?php

/**
 * ECGROUPON 管理中心城市管理
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("group_city"), $db, 'city_id', 'city_name');

/*------------------------------------------------------ */
//-- 城市列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{   
    admin_priv('view_city');
    $smarty->assign('ur_here',      $_LANG['group_city_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['group_city_add'], 'href' => 'group_city.php?act=add'));
    $smarty->assign('full_page',    1);

    $city_list = get_citylist();
    $smarty->assign('city_id',    $_CFG['group_city']);
    $smarty->assign('city_list',   $city_list['city']);
    $smarty->assign('filter',       $city_list['filter']);
    $smarty->assign('record_count', $city_list['record_count']);
    $smarty->assign('page_count',   $city_list['page_count']);
	 $sort_flag  = sort_flag($city_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('group_city_list.htm');
}

/*------------------------------------------------------ */
//-- 添加城市
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('add_city');

    $smarty->assign('ur_here',     $_LANG['group_city_add']);
    $smarty->assign('action_link', array('text' => $_LANG['group_city_list'], 'href' => 'group_city.php?act=list'));
    $smarty->assign('form_action', 'insert');
    assign_query_info();
    $smarty->assign('city', array('is_open'=>1,'is_select'=>0));
    $smarty->display('group_city_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    /*检查品牌名是否重复*/
    admin_priv('add_city');

    $is_open = isset($_REQUEST['is_open']) ? intval($_REQUEST['is_open']) : 0;
	$city_name = trim($_POST['city_name']);
    $eng_name = trim($_POST['eng_name']);

	if ($city_name == '')
	{
	   sys_msg($_LANG['no_cityname']);
	}
    $is_only_city = $exc->is_only('city_name', $city_name);

    if (!$is_only_city)
    {
        sys_msg(sprintf($_LANG['cityname_exist'], stripslashes($city_name)), 1);
    }
    if (!empty($eng_name))
    {
		$is_only_eng = $exc->is_only('eng_name', $eng_name);
		if (!$is_only_eng)
		{
          sys_msg(sprintf($_LANG['engname_exist'], stripslashes($eng_name)), 1);
		}
    }

    /*对描述处理*/
    if (!empty($_POST['city_desc']))
    {
        $_POST['city_desc'] = $_POST['city_desc'];
    }
    $city_sort = intval($_POST['city_sort']) >= 0 ? intval($_POST['city_sort']) : '0';
    $sql = "INSERT INTO ".$ecs->table('group_city').
	       "(city_id,city_name,city_desc,city_notice,is_open,city_title,city_keyword,city_qq,city_sort,eng_name) ".
           "VALUES ('$city_id','$city_name', '$_POST[city_desc]','$_POST[city_notice]','$is_open'" .            ",'$_POST[city_title]','$_POST[city_keyword]','$_POST[city_qq]','$city_sort','$eng_name')";
    $db->query($sql);
	if ($_POST['is_select'] == 1)
	{
       $sql = "UPDATE " . $ecs->table('shop_config') . " SET value = '$city_id' WHERE code = 'group_city'";
       $db->query($sql);
    }
    admin_log($city_name,'add','city');

    /* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'group_city.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'group_city.php?act=list';

    sys_msg($_LANG['cityadd_succed'], 0, $link);
}

elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('add_city');
    $sql = "SELECT * ".
            "FROM " .$ecs->table('group_city'). " WHERE city_id='$_REQUEST[id]'";
    $city = $db->GetRow($sql);
    $smarty->assign('ur_here',     $_LANG['group_city_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['group_city_list'], 'href' => 'group_city.php?act=list&' . list_link_postfix()));
	$city['is_select'] = '0';
	if ($city['city_id'] == $_CFG['group_city'])
	{
	  $city['is_select'] = '1';
	}
    $smarty->assign('city',       $city);

    $smarty->assign('form_action', 'updata');

    assign_query_info();
    $smarty->display('group_city_info.htm');
}
elseif ($_REQUEST['act'] == 'updata')
{
    admin_priv('add_city');
	$city_id = $_POST['id'];
    /*对描述处理*/
    if (!empty($_POST['city_desc']))
    {
        $_POST['city_desc'] = $_POST['city_desc'];
    }

    $is_open = isset($_REQUEST['is_open']) ? intval($_REQUEST['is_open']) : 0;
	$city_sort = intval($_POST['city_sort']) >= 0 ? intval($_POST['city_sort']) : '0';
	$eng_name = trim($_POST['eng_name']);
	if (!empty($eng_name))
    {
		$is_only_eng = $exc->is_only('eng_name', $eng_name);
		if (!$is_only_eng)
		{
          sys_msg(sprintf($_LANG['engname_exist'], stripslashes($eng_name)), 1);
		}
    }
    $param = "city_desc='$_POST[city_desc]', is_open='$is_open', city_notice='$_POST[city_notice]',eng_name='$eng_name'".
	",city_title='$_POST[city_title]',city_keyword='$_POST[city_keyword]',city_qq='$_POST[city_qq]',city_sort='$city_sort' ";
    if ($_POST['is_select'] == 1)
	{
       $sql = "UPDATE " . $ecs->table('shop_config') . " SET value = '$city_id' WHERE code = 'group_city'";
       $db->query($sql);
    }
    if ($exc->edit($param, $city_id))
    {
        /* 清除缓存 */
        clear_cache_files();

        admin_log($_POST['old_city_name'], 'edit', 'city');

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'group_city.php?act=list&' . list_link_postfix();
        $note = vsprintf($_LANG['cityedit_succed'], $_POST['old_city_name']);
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}


/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('add_city');

    $id     = intval($_POST['id']);
    $order  = intval($_POST['val']);
    $name   = $exc->get_name($id);

    if ($exc->edit("city_sort = '$order'", $id))
    {
        admin_log(addslashes($name),'edit','city');

        make_json_result($order);
    }
    else
    {
        make_json_error(sprintf($_LANG['cityedit_fail'], $name));
    }
}

/*------------------------------------------------------ */
//-- 是否开通
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_open')
{
    check_authz_json('add_city');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open='$val'", $id);

    make_json_result($val);
}
/*------------------------------------------------------ */
//-- 默认城市
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'select_city')
{
    check_authz_json('add_city');

    $city_id = intval($_GET['city_id']);
    $sql = "UPDATE " . $ecs->table('shop_config') . " SET value = '$city_id' WHERE code = 'group_city'";
    if ($db->query($sql))
	{
	  clear_cache_files();
	  $url = 'group_city.php?act=query&' . str_replace('act=select_city', '', $_SERVER['QUERY_STRING']);
      ecs_header("Location: $url\n");
      exit;
      //make_json_result($val);
	}
}

elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('remove_city');

    $id = intval($_GET['id']);
    if ($id == $_CFG['group_city'])
	{
	    $msg = '默认城市不能删除!';
		make_json_error($msg);
	}
    $exc->drop($id);

    $url = 'group_city.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
	//admin_priv('view_city');
    $city_list = get_citylist();
	$smarty->assign('city_id',    $_CFG['group_city']);
    $smarty->assign('city_list',   $city_list['city']);
    $smarty->assign('filter',       $city_list['filter']);
    $smarty->assign('record_count', $city_list['record_count']);
    $smarty->assign('page_count',   $city_list['page_count']);
	$sort_flag  = sort_flag($city_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('group_city_list.htm'), '',
        array('filter' => $city_list['filter'], 'page_count' => $city_list['page_count']));
}

/**
 * 获取城市列表
 *
 * @access  public
 * @return  array
 */
function get_citylist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();
		
        $filter['city_name'] = !empty($_REQUEST['city_name']) ? trim($_REQUEST['city_name']) : '';
		$where = '';
		if ($filter['city_name'] != '')
		{
			$where = " WHERE city_name like '%". mysql_like_quote($filter['city_name']) . "%'";
		}
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('group_city') . $where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'city_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter = page_and_size($filter);

        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('group_city')."$where  ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {

        $arr[] = $rows;
    }

    return array('city' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
