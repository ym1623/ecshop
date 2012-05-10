<?php

/**
 * ECGROUPON 管理中心城市管理
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
$exc = new exchange($ecs->table("group_seller"), $db, 'seller_id', 'user_name');

/*------------------------------------------------------ */
//-- 城市列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{   
    admin_priv('view_seller');
    $smarty->assign('ur_here',      $_LANG['group_seller_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['group_seller_add'], 'href' => 'group_seller.php?act=add'));
    $smarty->assign('full_page',    1);

    $seller_list = get_sellerlist();
    $smarty->assign('seller_list',   $seller_list['seller']);
	$smarty->assign('city_list',       get_group_city());
    $smarty->assign('filter',       $seller_list['filter']);
    $smarty->assign('record_count', $seller_list['record_count']);
    $smarty->assign('page_count',   $seller_list['page_count']);
	 $sort_flag  = sort_flag($seller_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('group_seller_list.htm');
}
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('view_seller');

    $id = intval($_GET['id']);
    $exc->drop($id);

    $url = 'group_seller.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
	//admin_priv('view_seller');
    $seller_list = get_sellerlist();
    $smarty->assign('seller_list',  $seller_list['seller']);
    $smarty->assign('filter',       $seller_list['filter']);
    $smarty->assign('record_count', $seller_list['record_count']);
    $smarty->assign('page_count',   $seller_list['page_count']);
	$sort_flag  = sort_flag($seller_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('group_seller_list.htm'), '',
        array('filter' => $seller_list['filter'], 'page_count' => $seller_list['page_count']));
}

/**
 * 获取城市列表
 *
 * @access  public
 * @return  array
 */
function get_sellerlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();
		
        $filter['city_id'] = !empty($_REQUEST['city_id']) ? trim($_REQUEST['city_id']) : '';
		$filter['seller_type'] = !empty($_REQUEST['seller_type']) ? trim($_REQUEST['seller_type']) : '0';
		$where = 'WHERE 1';
		if ($filter['city_id'] > '0')
		{
			$where = " WHERE gs.city_id = '$filter[city_id]'";
		}
		if ($filter['seller_type'] >= '0')
		{
			$where = " WHERE gs.seller_type = '$filter[seller_type]'";
		}
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('group_seller') ." AS gs " . $where;

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'gs.city_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter = page_and_size($filter);

        $sql = "SELECT *,gc.city_name FROM " . $GLOBALS['ecs']->table('group_seller'). " AS gs," . 
		       $GLOBALS['ecs']->table('group_city') .
		       " AS gc $where AND gc.city_id=gs.city_id  ORDER BY $filter[sort_by] $filter[sort_order]";
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

    return array('seller' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
