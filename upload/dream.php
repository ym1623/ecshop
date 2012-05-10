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
if ($_POST['act'] == 'add_seller')
{
    $seller = array();
    $seller['seller_phone'] = trim($_POST['seller_phone']);
    $seller['other_phone'] = trim($_POST['other_phone']);
	$seller['seller_name'] =  trim($_POST['seller_name']);
	if (empty($seller['seller_name']))
	{
	   show_group_message('请填写您的称呼!', '', "dream.php", 'error');
	}
	if (empty($seller['seller_phone']) && empty($seller['other_phone']))
	{
	   show_group_message('请填写您的联系方式!', '', "dream.php", 'error');
	}
    $seller['seller_content'] = trim($_POST['seller_content']);
	if (empty($seller['seller_content']))
	{
	   show_group_message('请填写您要团购的内容!', '', "dream.php", 'error');
	}
	$seller['city_id'] = intval($_POST['city_id']);
	$seller['from_ip'] = real_ip();
	$seller['seller_time'] = gmtime();
	$seller['seller_type'] = 1;
	if (!empty($seller))
	{
	  $res = $db->autoExecute($ecs->table('group_seller'), $seller, 'INSERT');
      if ($res)
      {
        show_group_message('信息提交成功,我们会尽快与您联系!', '', "dream.php", 'error');
      }
	}
	else
	{
		show_group_message('请把信息填写完整!', '', "dream.php", 'error');
	}

}
else
{
   $smarty->assign('class_list', group_class_list(1,true));
   $smarty->display('group_dream.dwt');
}
?>