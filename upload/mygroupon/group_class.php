<?php

/**
 * ECSHOP 商品分类管理程序
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: group_class.php 17063 2010-03-25 06:35:46Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');

$exc = new exchange($ecs->table("group_class"), $db, 'cid', 'class_name');

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
//-- 商品分类列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取分类列表 */
	$type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
    $cat_list = group_class_list($type);

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['03_category_list']);
    $smarty->assign('action_link',  array('href' => "group_class.php?act=add&type=$type", 'text' => $_LANG['04_category_add']));
    $smarty->assign('full_page',    1);
    $smarty->assign('type',         $type);
    $smarty->assign('cat_info',     $cat_list);
    /* 列表页面 */
    assign_query_info();
    $smarty->display('group_class_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $cat_list = group_class_list();
    $smarty->assign('cat_info',     $cat_list);

    make_json_result($smarty->fetch('group_class_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限检查 */
    admin_priv('cat_manage');
    $type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['04_category_add']);
    $smarty->assign('action_link',  array('href' => "group_class.php?act=list&type=$type", 'text' => $_LANG['03_category_list']));

    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1, 'class_type' => $type, 'sort_order' => '0'));
    /* 显示页面 */
    assign_query_info();
    $smarty->display('group_class_info.htm');
}

/*------------------------------------------------------ */
//-- 商品分类添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限检查 */
    admin_priv('cat_manage');

    /* 初始化变量 */
    $cat['cid']       = !empty($_POST['cid'])       ? intval($_POST['cid'])     : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['class_desc']     = !empty($_POST['class_desc'])     ? $_POST['class_desc']           : '';
    $cat['class_name']     = !empty($_POST['class_name'])     ? trim($_POST['class_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
    $cat['class_type']      = !empty($_POST['class_type'])      ? intval($_POST['class_type'])    : 1;
    
	if (cat_exists($cat['class_name'], $cat['class_type']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['catname_exist'], 0, $link);
    }

    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('group_class'), $cat) !== false)
    {
 		
        admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = $_LANG['continue_add'];
        $link[0]['href'] = "group_class.php?act=add&type=".$cat['class_type'];

        $link[1]['text'] = $_LANG['back_list'];
        $link[1]['href'] = "group_class.php?act=list&type=".$cat['class_type'];

        sys_msg($_LANG['catadd_succed'], 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('cat_manage');   // 权限检查
    $cat_id = intval($_REQUEST['cat_id']);
    $cat_info = get_cat_info($cat_id);  // 查询分类信息数据
 
    $smarty->assign('ur_here',     $_LANG['category_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['03_category_list'], 'href'=>"group_class.php?act=list&type=".$cat_info['class_type']));

    $smarty->assign('cat_info',    $cat_info);
    $smarty->assign('form_act',    'update');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('group_class_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{
    /* 权限检查 */
    admin_priv('cat_manage');

    /* 初始化变量 */
    $cat_id              = !empty($_POST['cid'])       ? intval($_POST['cid'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['class_desc']   = !empty($_POST['class_desc'])     ? $_POST['class_desc']           : '';
    $cat['class_name']   = !empty($_POST['class_name'])     ? trim($_POST['class_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
    $cat['class_type']   = !empty($_POST['class_type'])      ? intval($_POST['class_type'])    : 1;

    /* 判断分类名是否重复 */

    if ($cat['class_name'] != $old_cat_name)
    {
        if (cat_exists($cat['class_name'],$cat['class_type']))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }

    if ($db->autoExecute($ecs->table('group_class'), $cat, 'UPDATE', "cid='$cat_id'"))
    {
       
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => "group_class.php?act=list&type=".$cat['class_type']);
        sys_msg($_LANG['catedit_succed'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 批量转移商品分类页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move')
{
    /* 权限检查 */
    admin_priv('cat_drop');
    $type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
    $cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['move_goods']);
    $smarty->assign('action_link', array('href' => 'group_class.php?act=list&type='.$type, 'text' => $_LANG['03_category_list']));

    $smarty->assign('cat_select',  group_class_list($type));
    $smarty->assign('form_act',   'move_cat');
    $smarty->assign('type',         $type);
    /* 显示页面 */
    assign_query_info();
    $smarty->display('group_move.htm');
}

/*------------------------------------------------------ */
//-- 处理批量转移商品分类的处理程序
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move_cat')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
    $target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;
    $type = !empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
    /* 商品分类不允许为空 */
    if ($cat_id == 0 || $target_cat_id == 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'group_class.php?act=move');
        sys_msg($_LANG['cat_move_empty'], 0, $link);
    }

    /* 更新商品分类 */
    switch($type)
	{
		case 1:
		      $sql = "UPDATE " .$ecs->table('group_activity'). " SET cat_id = '$target_cat_id' ".
                      "WHERE cat_id = '$cat_id'";
			  break;
		case 2:
		      $sql = "UPDATE " .$ecs->table('suppliers'). " SET cid = '$target_cat_id' ".
                     "WHERE cat_id = '$cat_id'";
			  break;
		case 3:	  
		$sql = "UPDATE " .$ecs->table('group_forum'). " SET cid = '$target_cat_id' ".
               "WHERE cid = '$cat_id'";
			  break;	  
	}
    if ($db->query($sql))
    {
        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'group_class.php?act=list&type='.$type);
        sys_msg($_LANG['move_cat_success'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_show')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('is_show' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 删除商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('cat_manage');

    /* 初始化分类ID并取得分类名称 */
    $cat_id   = intval($_GET['id']);
    $cat_name = $db->getOne('SELECT class_name FROM ' .$ecs->table('group_class'). " WHERE cid='$cat_id'");

    /* 当前分类下是否存在商品 */
    $goods_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('group_activity'). " WHERE cat_id='$cat_id'");

    /* 如果不存在下级子分类和商品，则删除之 */
    if ($goods_count == 0)
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('group_class'). " WHERE cid = '$cat_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($cat_name, 'remove', 'category');
        }
    }
    else
    {
        make_json_error($cat_name .' '. $_LANG['cat_isleaf']);
    }

    $url = 'group_class.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
//
///**
// * 检查分类是否已经存在
// *
// * @param   string      $cat_name       分类名称
// *
// * @return  boolean
// */
function cat_exists($class_name, $class_type)
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('group_class').
          " WHERE class_type = '$class_type' AND class_name = '$class_name'";
    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
}

/**
 * 获得商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_cat_info($cid)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('group_class'). " WHERE cid='$cid' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cid, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('group_class'), $args, 'update', "cid='$cid'");
}

?>