<?php

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
if (!$smarty->is_cached('links.dwt', $cache_id))
{  
	assign_public($city_id);
	$links = links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
}
$smarty->display('links.dwt', $cache_id);
function links()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['ecs']->table('friend_link') . ' ORDER BY show_order';
    $res = $GLOBALS['db']->getAll($sql);
    $links['img'] = $links['txt'] = array();
    foreach ($res AS $row)
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
                                    'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url']);
        }
    }
    return $links;
}
?>