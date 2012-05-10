<?php

/**
 * ECGROUPON 管理中心一站通管理
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("login_config"), $db, 'config_id', 'web_name');

/*------------------------------------------------------ */
//-- 城市列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{   
    admin_priv('login_config');
    $smarty->assign('ur_here',      $_LANG['group_login_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['group_login_add'], 'href' => 'group_login.php?act=add'));
    $smarty->assign('full_page',    1);

    $login_list = get_loginlist();
    $smarty->assign('login_list',   $login_list['config']);
    $smarty->assign('filter',       $login_list['filter']);
    $smarty->assign('record_count', $login_list['record_count']);
    $smarty->assign('page_count',   $login_list['page_count']);
	 $sort_flag  = sort_flag($login_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('group_login_list.htm');
}

/*------------------------------------------------------ */
//-- 添加城市
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('login_config');

    $smarty->assign('ur_here',     $_LANG['group_login_add']);
    $smarty->assign('action_link', array('text' => $_LANG['group_login_list'], 'href' => 'group_login.php?act=list'));
    $smarty->assign('form_action', 'insert');
    assign_query_info();
    $smarty->assign('login', array('is_open'=>1));
    $smarty->display('group_login_info.htm');
}
elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('login_config');

    $is_open = $_POST['is_open'] > 0 ? 1 : 0;
	$web_name = trim($_POST['web_name']);
    $web_url = trim($_POST['web_url']);
	$web_login = trim($_POST['web_login']);
    $login_img = trim($_POST['login_img']);
    $web_from = trim($_POST['web_from']);
	$app_key = trim($_POST['app_key']);
	$app_secret = trim($_POST['app_secret']);
	$app_encrypt = trim($_POST['app_encrypt']);
	$sort_order = intval($_POST['sort_order']);
	if ($web_name == '')
	{
	   sys_msg($_LANG['no_webname']);
	}
    if ($web_from == '')
	{
	   sys_msg($_LANG['no_webfrom']);
	}

    $is_only_login = $exc->is_only('web_name', $web_name);

    if (!$is_only_login)
    {
        sys_msg(sprintf($_LANG['webname_exist'], stripslashes($web_name)), 1);
    }
    $is_only_login = $exc->is_only('web_from', $web_name);

    if (!$is_only_login)
    {
        sys_msg(sprintf($_LANG['webfrom_exist'], stripslashes($web_from)), 1);
    }
    
    $sql = "INSERT INTO ".$ecs->table('login_config').
	       "(web_name,web_url,web_from,is_open,app_key,app_secret,app_encrypt,web_login,login_img,sort_order) ".
           "VALUES ('$web_name', '$web_url','$web_from','$is_open','$app_key','$app_secret','$app_encrypt','$web_login','$login_img','$sort_order')";
    $db->query($sql);
    admin_log($web_name,'add','login');

    /* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'group_login.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'group_login.php?act=list';

    sys_msg($_LANG['loginadd_succed'], 0, $link);
}

elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('login_config');
    $sql = "SELECT * ".
            "FROM " .$ecs->table('login_config'). " WHERE config_id='$_REQUEST[id]'";
    $login = $db->GetRow($sql);
    $smarty->assign('ur_here',     $_LANG['group_login_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['group_login_list'], 'href' => 'group_loign.php?act=list&' . list_link_postfix()));
    $smarty->assign('login',       $login);

    $smarty->assign('form_action', 'updata');

    assign_query_info();
    $smarty->display('group_login_info.htm');
}
elseif ($_REQUEST['act'] == 'updata')
{
    admin_priv('login_config');
	$config_id = $_POST['id'];
    /*对描述处理*/
    $is_open = isset($_POST['is_open']) ? 1 : 0;
	$web_name = trim($_POST['web_name']);
    $web_url = trim($_POST['web_url']);
    $web_from = trim($_POST['web_from']);
	$web_login = trim($_POST['web_login']);
    $login_img = trim($_POST['login_img']);
	$app_key = trim($_POST['app_key']);
	$app_secret = trim($_POST['app_secret']);
	$app_encrypt = trim($_POST['app_encrypt']);
	$sort_order = intval($_POST['sort_order']);
	$sql = "SELECT app_encrypt FROM " .$ecs->table('login_config'). " WHERE config_id='$config_id'";
    $oldapp_encrypt = $db->getOne($sql);
    if (empty($oldapp_encrypt))
	{
	  $othersql = !empty($app_encrypt) ? ",app_encrypt='$app_encrypt'": '';
	}
	$param ="is_open='$is_open',web_name='$web_name',web_url='$web_url',web_from='$web_from',sort_order='$sort_order',".                     
	         "app_key='$app_key',app_secret='$app_secret',web_login='$web_login',login_img='$login_img'".$othersql;
	if ($web_name == '')
	{
	   sys_msg($_LANG['no_webname']);
	}
    if ($web_from == '')
	{
	   sys_msg($_LANG['no_webfrom']);
	}

    /*$is_only_login = $exc->is_only('web_name', $web_name);

    if (!$is_only_login)
    {
        sys_msg(sprintf($_LANG['webname_exist'], stripslashes($web_name)), 1);
    }
    $is_only_login = $exc->is_only('web_from', $web_name);

    if (!$is_only_login)
    {
        sys_msg(sprintf($_LANG['webfrom_exist'], stripslashes($web_from)), 1);
    }*/
    
    if ($exc->edit($param, $config_id))
    {
        /* 清除缓存 */
        clear_cache_files();

        admin_log($_POST['old_web_name'], 'edit', 'login');

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'group_login.php?act=list&' . list_link_postfix();
        $note = vsprintf($_LANG['webedit_succed'], $_POST['old_web_name']);
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}


/*------------------------------------------------------ */
//-- 是否开通
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_open')
{
    check_authz_json('login_config');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open='$val'", $id);

    make_json_result($val);
}
/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $login_list = get_loginlist();
    $smarty->assign('login_list',   $login_list['config']);
    $smarty->assign('filter',       $login_list['filter']);
    $smarty->assign('record_count', $login_list['record_count']);
    $smarty->assign('page_count',   $login_list['page_count']);
	$sort_flag  = sort_flag($login_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('group_login_list.htm'), '',
        array('filter' => $login_list['filter'], 'page_count' => $login_list['page_count']));
}
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('login_config');

    $id = intval($_GET['id']);
    $exc->drop($id);

    $url = 'group_login.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/**
 * 获取城市列表
 *
 * @access  public
 * @return  array
 */
function get_loginlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();
		
        $filter['web_name'] = !empty($_REQUEST['web_name']) ? trim($_REQUEST['web_name']) : '';
		$where = '';
		if ($filter['web_name'] != '')
		{
			$where = " WHERE web_name like '%". mysql_like_quote($filter['web_name']) . "%'";
		}
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('login_config') . $where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'config_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter = page_and_size($filter);

        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('login_config')."$where  ORDER BY $filter[sort_by] $filter[sort_order]";

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

    return array('config' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
