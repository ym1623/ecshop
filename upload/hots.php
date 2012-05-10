<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(ROOT_PATH . 'includes/lib_grouplist.php');

$smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
$smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['formwork'];

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$size = 16;
$page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
$cat_id = isset($_REQUEST['catid']) && intval($_REQUEST['catid']) > 0 ? intval($_REQUEST['catid']) : 0;
//$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
    $city_id  = (isset($_REQUEST['cityid']) && intval($_REQUEST['cityid']) > 0) ? intval($_REQUEST['cityid'])   : (isset($_COOKIE['ECS']['cityid']) ? $_COOKIE['ECS']['cityid'] :  $_CFG['group_city']);
    setcookie('ECS[cityid]', $city_id, gmtime() + 86400 * 7);
$cache_id = $_CFG['lang'] . '-' . $size . '-' . $page . '-' . $city_id . '-' . $cat_id;
$cache_id = sprintf('%X', crc32($cache_id));

    /* 如果没有缓存，生成缓存 */
if (!$smarty->is_cached('group_hots.dwt', $cache_id))
{
	    $count = group_buy_count($city_id,$cat_id,3);
        
		if ($count > 0)
        {
            /* 取得当前页的热销 */
            $group_list = group_buy_list($city_id,$cat_id,3,$size, $page);
            $smarty->assign('group_list',  $group_list);
            /* 设置分页链接 */
            $pager = get_group_pager('stage.php', array('catid'=>$cat_id,'cityid' => $city_id), $count, $page, $size);
            $smarty->assign('pager', $pager);
        }
        /*else
		{
		    $url = rewrite_groupurl('subscribe.php');
            ecs_header("Location: $url\n");
            exit; 
		}*/
        /* 模板赋值 */
		assign_public($city_id);
		$smarty->assign('where', 'hots');
	    $show_groupclass = !empty($_CFG['show_groupclass']) ? explode(',',$_CFG['show_groupclass']) : array();
	    if (in_array(1,$show_groupclass))
	    {
		  $smarty->assign('catid',  $cat_id);
		  $smarty->assign('class_url',  rewrite_groupurl('hots.php'));
		  $smarty->assign('class_list', group_class_list(1,true,'hots.php'));
        }
		$smarty->assign('today_group',  get_today_grouplist('0',$city_id, $cat_id));

}
$smarty->display('group_hots.dwt', $cache_id);

?>