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

if ((DEBUG_MODE & 2) != 2)
{
  $smarty->caching = true;
}
   $id = empty($_GET['id']) ? 36 : intval($_GET['id']);
   $city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
   $cache_id = $_CFG['lang'] . '-' . $id . '-' . $city_id;
   $cache_id = sprintf('%X', crc32($cache_id));
   if (!$smarty->is_cached('help.dwt', $cache_id))
   {
	    assign_public($city_id);
        $smarty->assign('action', $action);
		$smarty->assign('where', 'help');
		$smarty->assign('id', $id);
		$smarty->assign('article',  get_article_info($id));
   }
   $smarty->display('help.dwt', $cache_id);
   
   function get_article_info($article_id)
   {
	   $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('article') . " WHERE is_open = 1 AND article_id ='$article_id'";
	   return $GLOBALS['db']->getRow($sql);
   }

?>