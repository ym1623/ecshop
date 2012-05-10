<?php
/**
 * ECgroupon 管理中心团购商品管理
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
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
//-- 团购活动列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 模板赋值 */
    admin_priv('view_group');
    $smarty->assign('full_page',    1);
    $smarty->assign('ur_here',      $_LANG['group_buy_list']);
    $smarty->assign('action_link',  array('href' => 'group_buy.php?act=add', 'text' => $_LANG['add_group_buy']));
    $list = group_buy_list();
    $suppliers_list_name = get_big_suppliers();
    $smarty->assign('city_list',       get_group_city());
	$smarty->assign('suppliers_list_name', $suppliers_list_name);
    $smarty->assign('cat_list',         group_class_list());
    $smarty->assign('group_buy_list',   $list['item']);
    $smarty->assign('filter',           $list['filter']);
    $smarty->assign('record_count',     $list['record_count']);
    $smarty->assign('page_count',       $list['page_count']);
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    /* 显示商品列表页面 */
    assign_query_info();
    $smarty->display('group_buy_list.htm');
}
elseif ($_REQUEST['act'] == 'query')
{
    //admin_priv('view_group');
    $list = group_buy_list();
    $smarty->assign('group_buy_list', $list['item']);
    $smarty->assign('filter',         $list['filter']);
    $smarty->assign('record_count',   $list['record_count']);
    $smarty->assign('page_count',     $list['page_count']);
    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('group_buy_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/*------------------------------------------------------ */
//-- 添加/编辑团购活动
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit' || $_REQUEST['act'] == 'copy')
{
    /* 初始化/取得团购活动信息 */
	include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件
    if ($_REQUEST['act'] == 'add')
    {
	    admin_priv('add_group');	
        $group_buy = array(
            'group_id'  => 0,
			'group_need' => 1,
			'is_limit' => 1,
			'is_show' => 1,
			'group_type' => 2,
			'group_attr' => 0,
			'other_city' => array(),
            'start_time'    => date('Y-m-d', time() + 86400),
            'end_time'      => date('Y-m-d', time() + 4 * 86400),
			'past_time'      => date('Y-m-d', time() + 4 * 86400),
            'price_ladder'  => array(array('amount' => 1, 'price' => ''))
        );
    }
    else
    {
        admin_priv('edit_group');
        $group_buy_id = intval($_REQUEST['id']);
        if ($group_buy_id <= 0)
		{
           die('invalid param');
        }
        $group_buy = get_groupbuy_info($group_buy_id);
       	$smarty->assign('group_attr_html', build_group_attr_html($group_buy['group_attr'], $group_buy['group_id']));
        $smarty->assign('group_supp_html', build_group_supp_html($group_buy['suppliers_id'], $group_buy['group_id']));
        $sql = "SELECT * FROM " . $ecs->table('group_gallery') . " WHERE group_id = '$group_buy_id'";
        $img_list = $db->getAll($sql);
	    $smarty->assign('img_list', $img_list);  
		$smarty->assign('friend_comment',get_friend_comment($group_buy_id));
		$sql = "SELECT city_id FROM " . $ecs->table('expand_city') . " WHERE group_id = '$group_buy_id'";
        $group_buy['other_city'] = $db->getCol($sql);
    }
    $group_buy['other_city'] = !empty($group_buy['other_city']) ? $group_buy['other_city'] : array();
    $smarty->assign('group_buy', $group_buy);
	create_html_editor('group_desc', $group_buy['group_desc']);
	create_basic_editor('goods_comment',$group_buy['goods_comment']);
	create_basic_editor('group_comment',$group_buy['group_comment']);
	create_basic_editor('group_brief',$group_buy['group_brief']);
    /* 模板赋值 */
    $smarty->assign('ur_here', $_LANG['add_group_buy']);
    $smarty->assign('action_link', list_link($_REQUEST['act'] == 'add'));
    $smarty->assign('cat_list', group_class_list());
	$smarty->assign('group_attr_list', goods_type_list($group_buy['group_attr']));
	$city_list = get_group_city();
	foreach ($city_list as $key => $city)
	{
	   if (in_array($city['city_id'],$group_buy['other_city']))
	   {
	    $city_list[$key]['selected'] = 1;
	   }
	   else
	   {
		 $city_list[$key]['selected'] = 0; 
	   }
	}
    $smarty->assign('city_list', $city_list);
    $smarty->assign('shipping_list',shipping_list());
	$suppliers_list_name = get_big_suppliers();
    $smarty->assign('suppliers_list_name', $suppliers_list_name);
    unset($suppliers_list_name);
    $post_type = $_REQUEST['act'] == 'copy' ? 'copy_group' : '';
    $smarty->assign('post_type', $post_type); 
    /* 显示模板 */
    assign_query_info();
    $smarty->display('group_buy_info.htm');
}
/*------------------------------------------------------ */
//-- 添加/编辑团购活动的提交
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] =='insert_update')
{
    /* 取得团购活动id */
    $group_buy_id = intval($_POST['group_id']) > 0 ? intval($_POST['group_id']) : '0';
    /* 保存团购信息 */
    $group_name = empty($_POST['group_name']) ? '' : sub_str($_POST['group_name'], 0, 255, false);
	if ($group_name == '')
	{
	   sys_msg($_LANG['js_languages']['error_group_name']);
	}
	$market_price = intval($_POST['market_price']);
    if (!is_numeric($market_price) || $market_price <= 0)
	{
	   sys_msg($_LANG['js_languages']['error_market_price']);
	}
	/* 检查开始时间和结束时间是否合理 */
   $activity_type = intval($_POST['activity_type']);
   $start_time = local_strtotime($_POST['start_time']);
   $end_time = local_strtotime($_POST['end_time']);
   $past_time = local_strtotime($_POST['past_time']);
   if ($activity_type != 3)
   {
     if ($start_time == '' && $end_time == '')
     {
        sys_msg($_LANG['error_time']);
     }
     if ($start_time >= $end_time)
     {
       sys_msg($_LANG['invalid_time']);
     }
   }
	$goods_type = intval($_POST['goods_type']);
	$goods_name = trim($_POST['goods_name']);
   if ($goods_type == 1)
   {
	   if ($goods_name == '')
	   {
	     sys_msg($_LANG['error_goods_name']);
	   }
	   if ($end_time > $past_time)
	   {
	      sys_msg($_LANG['error_past_time']);
	   }
    }
	$group_restricted = $_POST['group_restricted'] > 0 ? intval($_POST['group_restricted']) : 0;
    $pos_express = $_POST['pos_express'] > 0 ? trim($_POST['pos_express']) : 0;
    $gift_integral = intval($_POST['gift_integral']) > 0 ? intval($_POST['gift_integral']) : 0;
    $price_ladder = array();
    $count = count($_POST['ladder_amount']);
	$is_one = false;
    for ($i = $count - 1; $i >= 0; $i--)
    {
        $amount = intval($_POST['ladder_amount'][$i]);
		if ($amount == 1)
		{
		  $is_one = $_POST['ladder_price'][$i] != '' ? true : false;
		}
        $price = round(floatval($_POST['ladder_price'][$i]), 2);
        $price_ladder[$amount] = array('amount' => $amount, 'price' => $price);
    }
	if (!$is_one)
	{
	  sys_msg($_LANG['js_languages']['error_ladder_amount']);
	}
    if (count($price_ladder) < 1)
    {
       sys_msg($_LANG['error_price_ladder']);
	}
   /* 限购数量不能小于价格阶梯中的最大数量 */
   $amount_list = array_keys($price_ladder);
   if ($group_restricted > 0 && max($amount_list) > $group_restricted)
   {
      sys_msg($_LANG['error_restrict_amount']);
   }
   if ($group_restricted > 0 && $pos_express > $group_restricted)
   {
	   sys_msg($_LANG['error_group_restricted']);	
   }
   ksort($price_ladder);
   $price_ladder = array_values($price_ladder);
   $city_id = intval($_POST['city_id']);
   $suppliers_id = intval($_POST['suppliers_id']);
   $lower_orders = intval($_POST['lower_orders']) > 0 ? intval($_POST['lower_orders']) : 0;
   $upper_orders = intval($_POST['upper_orders']) > 0 ? intval($_POST['upper_orders']) : 0;
   if ($city_id <= 0)
   {
     sys_msg($_LANG['js_languages']['error_city_id']);
   }
   if ($suppliers_id <= 0)
   {
     sys_msg($_LANG['js_languages']['error_suppliers_id']);
   }
   if ($lower_orders <= 0)
   {
      sys_msg($_LANG['error_lower_orders']);
   }
   if ($upper_orders > 0 && $lower_orders > $upper_orders)
   {
       sys_msg($_LANG['error_upper_orders']);
   }
   $goods_rebate = intval($_POST['goods_rebate']) > 0 ? intval($_POST['goods_rebate']) : 0;
   $group_stock =intval($_POST['group_stock']) > 0 ? intval($_POST['group_stock']) : 0;
   $group_freight = intval($_POST['group_freight']) > 0 ? intval($_POST['group_freight']) : 0;
   $group_type = intval($_POST['group_type']);
   $cat_id = intval($_POST['cat_id']);  
   $group_keywords = trim($_POST['group_keywords']);
   $group_description = trim($_POST['group_description']);
   $group_comment = trim($_POST['group_comment']);
   $goods_comment = trim($_POST['goods_comment']);
   $group_brief = trim($_POST['group_brief']);
   $group_attr = trim($_POST['group_attr']);
   $small_desc = trim($_POST['small_desc']);
   $group_need = intval($_POST['group_need']);
   $already_orders = intval($_POST['already_orders']);
   $goods_weight = intval($_POST['goods_weight']);
   $shipping_id = intval($_POST['shipping_id']);
   $is_hdfk =intval($_POST['is_hdfk']) > 0 ? 1 : 0;
   $is_limit =intval($_POST['is_limit']) > 0 ? 1 : 0;
   $is_show =intval($_POST['is_show']) > 0 ? 1 : 0;
   $group_type =intval($_POST['group_type']) > 1 ? 2 : 1;
   $sort_order = intval($_POST['sort_order']);
   $group_buy = array(
            'group_name'   => $group_name,
			'goods_name'   => $goods_name,
            'group_desc'   => trim($_POST['group_desc']),
            'group_type'   => $group_type,
            'start_time'    => $start_time,
            'end_time'      => $end_time,
			'past_time'     => $past_time,
			'city_id'		=> $city_id,
			'upper_orders'  => $upper_orders,
			'lower_orders'  => $lower_orders,
			'pos_express'   => $pos_express,
			'market_price'  => $market_price,
			'suppliers_id'  => $suppliers_id,
			'group_keywords'=> $group_keywords,
			'goods_type'    => $goods_type,
			'group_description' => $group_description,
			'group_comment'  => $group_comment,
			'goods_comment'  => $goods_comment,
			'group_brief'    => $group_brief,
			'goods_rebate'   => $goods_rebate,
			'cat_id'		 => $cat_id,
			'group_need'     => $group_need,
			'group_stock'    => $group_stock,
			'group_attr'     => $group_attr,
			'small_desc'	 => $small_desc,
			'group_freight'  => $group_freight,
			'group_restricted' => $group_restricted,
			'already_orders' => $already_orders,
			'activity_type' => $activity_type,
			 'shipping_id'  => $shipping_id,
			 'goods_weight' => $goods_weight,
			 'succeed_time' => $succeed_time,
			 'is_hdfk'      => $is_hdfk,
			 'is_limit'     => $is_limit,
			 'is_show'     => $is_show,
			 'sort_order'  => $sort_order,
			        'ext_info'   => serialize(array(
                    'price_ladder'      => $price_ladder,
                    'gift_integral'     => $gift_integral
                    ))
        );
  
	$old_end_time = $_POST['old_end_time'];
	$now = gmtime();	
    if ($old_end_time < $now && $end_time > $now)
	{
        $group_buy['is_finished'] = '0';
		$group_buy['closed_time'] = '0';
    }
	if ($now > $end_time && $activity_type != 3)
	{
        $group_buy['is_finished'] = '2';
		$group_buy['closed_time'] = $now;
    }
	if ($group_buy_id <= 0 && ($activity_type == 2 || $activity_type == 3))
    {
      $group_buy['succeed_time'] = gmtime();
    } 
	$friend_name_arr = $_POST['friend_name'];
	$friend_web_arr = $_POST['friend_web'];
	$friend_url_arr = $_POST['friend_url'];
	$friend_desc_arr = $_POST['friend_desc'];
    $is_upload_image = false;
    if (isset($_FILES['group_image']) && $_FILES['group_image']['tmp_name'] != '' &&
            isset($_FILES['group_image']['tmp_name']) &&$_FILES['group_image']['tmp_name'] != 'none')
    {
         // 上传了，直接使用，原始大小
          $group_image = $image->upload_image($_FILES['group_image']);
          if ($group_image === false)
          {
            sys_msg($image->error_msg(), 1, array(), false);
          }
		  $is_upload_image = true;
    }
    if ($is_upload_image == true)
	{
       $group_buy['group_image'] = $group_image;
    }
	if ($_POST['post_type'] == 'copy_group')
	{
	   $old_buy_id = $group_buy_id;	
	   $group_buy_id = 0;
	   $group_buy['is_finished'] = '0';
	}		
        /* 保存数据 */
    if ($group_buy_id > 0)
    {
       if (isset($_POST['old_img_desc']))
       {
          foreach ($_POST['old_img_desc'] AS $img_id => $img_desc)
          {
			 $sql = "UPDATE " . $ecs->table('group_gallery') . " SET img_desc = '$img_desc' WHERE img_id = '$img_id' LIMIT 1";
             $db->query($sql);
          }
	    }
        $db->autoExecute($ecs->table('group_activity'), $group_buy, 'UPDATE', "group_id = '$group_buy_id'");
         /* log */
        admin_log(addslashes($group_name) . '[' . $group_buy_id . ']', 'edit', 'group_buy');
     }
     else
     {
         $db->autoExecute($ecs->table('group_activity'), $group_buy, 'INSERT');
		 $group_buy_id = $db->insert_id();
         admin_log(addslashes($group_name), 'add', 'group_buy');
      }
	   if ($_POST['post_type'] == 'copy_group')
	 {
	   if (!$is_upload_image)
	   {	 
	     $group_image = $db->getOne('SELECT group_image FROM' . $ecs->table('group_activity') . " WHERE group_id = '$old_buy_id'");
         $pos        = strpos(basename($group_image), '.');
         $newname    = dirname($group_image) . '/' . $image->random_filename() . substr(basename($group_image), $pos);
         if (!copy('../' . $group_image, '../' . $newname))
		 {
		   $newname = $group_image;
		 }
	     $sql = "UPDATE " . $ecs->table('group_activity') . " SET group_image = '$newname' WHERE group_id = '$group_buy_id' LIMIT 1";
         $db->query($sql);
	   }
	   if (count($_FILES['img_url']['name']) == 1 && $_FILES['img_url']['name'][0] == 0)
	   {
	    $sql = 'SELECT img_url,img_desc FROM' . $ecs->table('group_gallery') . " WHERE group_id = '$old_buy_id'";
	    $res = $GLOBALS['db']->query($sql);
	    while ($row = $GLOBALS['db']->fetchRow($res))
        {
		   $img_url = $row['img_url'];
		   $img_desc = $row['img_desc'];
		   $pos        = strpos(basename($img_url), '.');
           $newname    = dirname($img_url) . '/' . $image->random_filename() . substr(basename($img_url), $pos);
           if (!copy('../' . $img_url, '../' . $newname))
		   {
		     $newname = $img_url;
		   }
		   $sql = "INSERT INTO " . $GLOBALS['ecs']->table('group_gallery') . " (group_id, img_url, img_desc) " .
                    "VALUES ('$group_buy_id', '$newname', '$img_desc')";
             $GLOBALS['db']->query($sql);
	    }
	   }
	 }	
	     $other_city = empty($_POST['other_city'])?array():array_unique($_POST['other_city']);
		 handle_friend_comment($group_buy_id, $friend_name_arr, $friend_url_arr,$friend_web_arr,$friend_desc_arr);
		 handle_group_image($group_buy_id, $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file']);
         add_other_city($group_buy_id, $other_city);
		 add_other_suppliers($group_buy_id, $_POST['other_suppliers']);
	if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list'])))
    {
       // 取得原有的属性值
        $group_attr_list = array();
        $sql = "SELECT attr_id, attr_index FROM " . $ecs->table('attribute') . " WHERE cat_id = '$group_buy_id'";
        $attr_res = $db->query($sql);
        $attr_list = array();
        while ($row = $db->fetchRow($attr_res))
        {
           $attr_list[$row['attr_id']] = $row['attr_index'];
        }
        $sql = "SELECT g.*, a.attr_type
                FROM " . $ecs->table('group_attr') . " AS g
                    LEFT JOIN " . $ecs->table('attribute') . " AS a
                        ON a.attr_id = g.attr_id
                WHERE g.group_id = '$group_buy_id'";
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
          $group_attr_list[$row['attr_id']][$row['attr_value']] = array('sign' => 'delete', 'group_attr_id' => $row['group_attr_id']);
        }
        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {
            foreach ($_POST['attr_id_list'] AS $key => $attr_id)
            {
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
                if (!empty($attr_value))
                {
                    if (isset($group_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $group_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $group_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $group_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $group_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                }
            }
        }
        /* 插入、更新、删除数据 */
        foreach ($group_attr_list as $attr_id => $attr_value_list)
        {
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO " .$ecs->table('group_attr'). " (attr_id, group_id, attr_value, attr_price)".
                            "VALUES ('$attr_id', '$group_buy_id', '$attr_value', '$info[attr_price]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$ecs->table('group_attr'). " SET attr_price = '$info[attr_price]' WHERE group_attr_id = '$info[group_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$ecs->table('group_attr'). " WHERE group_attr_id = '$info[group_attr_id]' LIMIT 1";
                }
                $db->query($sql);
              }
            }
        }
		   /* 清除缓存 */
        clear_cache_files();
        if ($group_buy_id > 0)
		{  
		      /* 提示信息 */
            $links = array(
                array('href' => 'group_buy.php?act=edit&id=' . $group_buy_id, 'text' => $_LANG['back_list'])
            );
            sys_msg($_LANG['edit_success'], 0, $links);		  
		}
		else
		{		   
          /* 提示信息 */
            $links = array(
                array('href' => 'group_buy.php?act=add', 'text' => $_LANG['continue_add']),
                array('href' => 'group_buy.php?act=list', 'text' => $_LANG['back_list'])
            );
            sys_msg($_LANG['add_success'], 0, $links);
		}
}
/*------------------------------------------------------ */
//-- 批量删除团购活动
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_drop')
{
    admin_priv('remove_group');
    if (isset($_POST['checkboxes']))
    {
        $del_count = 0; //初始化删除数量
        foreach ($_POST['checkboxes'] AS $key => $id)
        {
            /* 取得团购活动信息 */
            $sql = "SELECT COUNT(*) " .
                   "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
                   " WHERE o.extension_code = 'group_buy' " .
                   "AND o.extension_id = '$id' " .
                   "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";
            $valid_order = $GLOBALS['db']->getOne($sql);
            /* 如果团购活动已经有订单，不能删除 */
            if ($valid_order <= 0)
            {
                /* 删除团购活动 */
			   $sql = "SELECT img_url FROM " . $ecs->table('group_gallery') . " WHERE group_id = '$id'";
               $res = $db->query($sql);
               while ($row = $db->fetchRow($res))
               {
                     if (!empty($row['img_url']) && is_file('../' . $row['img_url']))
                     {
                         @unlink('../' . $row['img_url']);
                     }
                }       
               $sql = "DELETE FROM " . $ecs->table('group_gallery') . " WHERE group_id = '$id'";
               $db->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('group_activity') .
                        " WHERE group_id = '$id' LIMIT 1";
				$sql = "DELETE FROM " . $ecs->table('comment') . " WHERE id_value='$id' AND comment_type=2";
	            $db->query($sql);
                $GLOBALS['db']->query($sql, 'SILENT');
                if (!empty($group_buy['group_imgae']) && is_file('../' . $group_buy['group_imgae']))
                {
                     @unlink('../' . $group_buy['group_imgae']);
                }
                admin_log(addslashes($group_buy['group_name']) . '[' . $id . ']', 'remove', 'group_buy');
                $del_count++;
            }
        }
        /* 如果删除了团购活动，清除缓存 */
        if ($del_count > 0)
        {
            clear_cache_files();
        }
        $links[] = array('text' => $_LANG['back_list'], 'href'=>'group_buy.php?act=list');
        sys_msg(sprintf($_LANG['batch_drop_success'], $del_count), 0, $links);
    }
    else
    {
        $links[] = array('text' => $_LANG['back_list'], 'href'=>'group_buy.php?act=list');
        sys_msg($_LANG['no_select_group_buy'], 0, $links);
    }
}
/*------------------------------------------------------ */
//-- 删除团购活动
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('remove_group');
    $id = intval($_GET['id']);
     $sql = "SELECT COUNT(*) " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
            " WHERE o.extension_code = 'group_buy' " .
            "AND o.extension_id = '$id' " .
            "AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";
    $valid_order = $GLOBALS['db']->getOne($sql);
    /* 如果团购活动已经有订单，不能删除 */
    if ($valid_order > 0)
    {
        make_json_error($_LANG['error_exist_order']);
    }
    /* 删除团购活动 */
    $sql = "DELETE FROM " . $ecs->table('group_activity') . " WHERE group_id = '$id' LIMIT 1";
    $db->query($sql);
    $sql = "SELECT img_url FROM " . $ecs->table('group_gallery') .
            " WHERE group_id = '$id'";
    $res = $db->query($sql);
	if (!empty($group_buy['group_imgae']) && is_file('../' . $group_buy['group_imgae']))
    {
       @unlink('../' . $group_buy['group_imgae']);
    }
    while ($row = $db->fetchRow($res))
    {
        if (!empty($row['img_url']) && is_file('../' . $row['img_url']))
        {
            @unlink('../' . $row['img_url']);
        }
    }
    $sql = "DELETE FROM " . $ecs->table('group_gallery') . " WHERE group_id = '$id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('comment') . " WHERE id_value='$id' AND comment_type=2";
	$db->query($sql);
    admin_log(addslashes($group_buy['group_name']) . '[' . $id . ']', 'remove', 'group_buy');
    clear_cache_files();
    $url = 'group_buy.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);
    ecs_header("Location: $url\n");
    exit;
}
elseif ($_REQUEST['act'] == 'get_attr')
{
    check_authz_json('view_group');
    $group_id   = empty($_GET['group_id']) ? 0 : intval($_GET['group_id']);
    $group_attr = empty($_GET['group_attr']) ? 0 : intval($_GET['group_attr']);
    $content    = build_group_attr_html($group_attr, $group_id);
	//$content = $group_id;
    make_json_result($content);
}
elseif ($_REQUEST['act'] == 'get_Supp')
{
    check_authz_json('view_group');
    $group_id   = empty($_GET['group_id']) ? 0 : intval($_GET['group_id']);
    $parent_id = empty($_GET['parent_id']) ? 0 : intval($_GET['parent_id']);
	$content = build_group_supp_html($parent_id,$group_id);
    make_json_result($content);
}
elseif ($_REQUEST['act'] == 'drop_image')
{
    check_authz_json('remove_group');
    $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);
    /* 删除图片文件 */
    $sql = "SELECT  img_url " .
            " FROM " . $GLOBALS['ecs']->table('group_gallery') .
            " WHERE img_id = '$img_id'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row['img_url'] != '' && is_file('../' . $row['img_url']))
    {
        @unlink('../' . $row['img_url']);
    }
    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('group_gallery') . " WHERE img_id = '$img_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
    clear_cache_files();
    make_json_result($img_id);
}
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('edit_group');
    $group_id = intval($_POST['id']);
    $sort_order = json_str_iconv(trim($_POST['val']));
    $sql = "UPDATE " . $ecs->table('group_activity') . " SET sort_order='$sort_order' WHERE group_id='$group_id'";
    if ($db->query($sql))
    {
        clear_cache_files();
        make_json_result(stripslashes($sort_order));
    }
}
elseif ($_REQUEST['act'] == 'toggle_is_show')
{
    check_authz_json('edit_group');

    $group_id       = intval($_POST['id']);
    $is_show        = intval($_POST['val']);

    $sql = "UPDATE " . $ecs->table('group_activity') . " SET is_show='$is_show' WHERE group_id='$group_id'";
    if ($db->query($sql))
    {
        clear_cache_files();
        make_json_result($is_show);
    }
}
elseif ($_REQUEST['act'] == 'get_excel')
{
     admin_priv('download_card');
    @set_time_limit(0);
    $group_id  = !empty($_GET['group_id']) ? intval($_GET['group_id']) : 0;
    /* 文件名称 */
    $group_filename = 'cards_' . date('Ymd');
    if (EC_CHARSET != 'gbk')
    {
        $group_filename = ecs_iconv('UTF8', 'GB2312',$group_filename);
    }
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$group_filename.xls");
    /* 文件标题 */
    if (EC_CHARSET != 'gbk')
    {   
	    echo ecs_iconv('UTF8', 'GB2312', $_LANG['user_name']) ."\t";
		echo ecs_iconv('UTF8', 'GB2312', $_LANG['user_mobile']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['card_sn']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['card_password']) ."\t";
        echo ecs_iconv('UTF8', 'GB2312', $_LANG['label_past_time']) ."\t\n";
    }
    else
    {
		echo $_LANG['user_name'] ."\t";
		echo $_LANG['user_mobile'] ."\t";
        echo $_LANG['card_sn'] ."\t";
        echo $_LANG['card_password'] ."\t";
        echo $_LANG['label_past_time'] ."\t\n";
    }
    $val = array();
    $sql = "SELECT gc.card_sn, gc.card_password,o.mobile,o.tel,u.user_name,gc.end_date,gc.order_sn ".
           "FROM ".$ecs->table('group_card')." AS gc, ".$ecs->table('order_info')." AS o, ". $ecs->table('users')." AS u " .
           "WHERE gc.order_sn = o.order_sn AND u.user_id=gc.user_id AND gc.group_id = '$group_id' AND gc.is_saled=1 AND gc.user_id=0 ORDER BY gc.group_id DESC ";
    $res = $db->query($sql);
    while ($val = $db->fetchRow($res))
    {
		echo ecs_iconv('UTF8', 'GB2312', $val['user_name']) ."\t";
        echo $val['mobile'] . "\t";
        echo $val['card_sn'] . "\t";
		echo $val['card_password'] . "\t";
        echo local_date('Y-m-d', $val['end_date']);
        echo "\t\n";
    }
}
/*
 * 取得团购活动列表
 * @return   array
 */
function group_buy_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤条件 */
		$filter['cat_id']       = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['city_id']      = empty($_REQUEST['city_id']) ? 0 : intval($_REQUEST['city_id']);
		$filter['activity_type']= in_array($_REQUEST['activity_type'],array(1,2,3)) ? intval($_REQUEST['activity_type']) : '1';
		$filter['group_stat']   = isset($_REQUEST['group_stat']) ? intval($_REQUEST['group_stat']) : -1 ;
        $filter['suppliers_id'] = isset($_REQUEST['suppliers_id']) ? (empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id'])) : '';
        $filter['keyword']      = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1)
        {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'group_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
        $where = $filter['cat_id'] > 0 ? "cat_id = '" . $filter['cat_id'] . "'" : '1';
        if ($filter['suppliers_id'] > 0)
        {
            $where .= " AND suppliers_id = '" . $filter['suppliers_id'] . "'";
        }
        if ($filter['city_id'] > 0)
        {
            $where .= " AND city_id = '" . $filter['city_id'] . "'";
        }
		if ($filter['activity_type'] > 0)
        {
            $where .= " AND activity_type = '" . $filter['activity_type'] . "'";
        }
        if (!empty($filter['keyword']))
		{
            $where .=  " AND group_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
		}
		if ($filter['start_time'])
        {
            $where .= " AND start_time >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND end_time <= '$filter[end_time]'";
        }
		$now = gmtime();
		switch($filter['group_stat'])
		{
			case 0:
			$where .= " AND start_time > $now AND is_finished=0";
			break;
			case 1:
			$where .= " AND start_time < $now AND end_time >= $now AND is_finished=0";
			break; 
		    case 2:
			$where .= " AND is_finished=2";
			break;
			case 3:
			$where .= " AND start_time < $now AND end_time >= $now AND succeed_time > 0 AND is_finished=0";
			break;
			case 4:
			$where .= " AND is_finished=4";
			break;
		   	case 5:
			$where .= " AND is_finished=5";
			break;
		}
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('group_activity') .
                " WHERE  $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        /* 分页大小 */
        $filter = page_and_size($filter);
        /* 查询 */
        $sql = "SELECT group_id,group_name,start_time,end_time,ext_info,group_type,upper_orders,goods_type,is_show,".
                "is_finished,sort_order FROM " . $GLOBALS['ecs']->table('group_activity') .
                " WHERE  $where ".
                " ORDER BY $filter[sort_by] $filter[sort_order] ".
                " LIMIT ". $filter['start'] .", $filter[page_size]";
        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->query($sql);
    $list = array();
    while ($arr = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($arr['ext_info']);
        $stat = get_group_buy_stat($arr['group_id']);
        $arr = array_merge($arr, $stat, $ext_info);
        /* 处理价格阶梯 */
        $price_ladder = $arr['price_ladder'];
        if (!is_array($price_ladder) || empty($price_ladder))
        {
            $price_ladder = array(array('amount' => 0, 'price' => 0));
        }
        else
        {
            foreach ($price_ladder AS $key => $amount_price)
            {
                $price_ladder[$key]['formated_price'] = price_format($amount_price['price']);
            }
        }
        /* 计算当前价 */
        $cur_price  = $price_ladder[0]['price'];    // 初始化
        $cur_amount = $stat['valid_goods'];         // 当前数量
        foreach ($price_ladder AS $amount_price)
        {
            if ($cur_amount >= $amount_price['amount'])
            {
                $cur_price = $amount_price['price'];
            }
            else
            {
                break;
            }
        }
        $arr['cur_price']   = $cur_price;
        $status = get_group_buy_status($arr);
        $arr['start_time']  = local_date($GLOBALS['_CFG']['date_format'], $arr['start_time']);
        $arr['end_time']    = local_date($GLOBALS['_CFG']['date_format'], $arr['end_time']);
        $arr['cur_status']  = $GLOBALS['_LANG']['gbs'][$status];
        $list[] = $arr;
    }
    $arr = array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
/**
 * 列表链接
 * @param   bool    $is_add         是否添加（插入）
 * @return  array('href' => $href, 'text' => $text)
 */
function list_link($is_add = true)
{
    $href = 'group_buy.php?act=list';
    if (!$is_add)
    {
        $href .= '&' . list_link_postfix();
    }
    return array('href' => $href, 'text' => $GLOBALS['_LANG']['group_buy_list']);
}
function handle_friend_comment($group_id, $friend_name_arr, $friend_url_arr,$friend_web_arr,$friend_desc_arr)
{
    if ($group_id > 0 && count($friend_desc_arr) > 0)
	{	
      $sql = "DELETE FROM " . $GLOBALS['ecs']->table('friend_comment') .
           " WHERE group_id = '$group_id'";
      $GLOBALS['db']->query($sql);
      foreach ($friend_desc_arr AS $key => $friend_desc)
      {
        $friend_name = $friend_name_arr[$key];
		$friend_url = $friend_url_arr[$key];
		$friend_web = $friend_web_arr[$key];
        if (!empty($friend_desc))
        {
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('friend_comment') .
                   " (friend_desc, group_id, friend_name, friend_url,friend_web) " .
                   "VALUES ('$friend_desc', '$group_id', '$friend_name', '$friend_url','$friend_web')";
            $GLOBALS['db']->query($sql);
        }
     }
   }
}
function group_attr_list($cat_id, $group_id = 0)
{
    if (empty($cat_id))
    {
        return array();
    }
    // 查询属性值及商品的属性值
    $sql = "SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type, a.attr_values, v.attr_value, v.attr_price ".
            "FROM " .$GLOBALS['ecs']->table('attribute'). " AS a ".
            "LEFT JOIN " .$GLOBALS['ecs']->table('group_attr'). " AS v ".
            "ON v.attr_id = a.attr_id AND v.group_id = '$group_id' ".
            "WHERE a.cat_id = " . intval($cat_id) ." OR a.cat_id = 0 ".
            "ORDER BY a.sort_order, a.attr_type, a.attr_id, v.attr_price, v.group_attr_id";
    $row = $GLOBALS['db']->GetAll($sql);
    return $row;
}
function build_group_attr_html($cat_id, $group_id = 0)
{
    $attr = group_attr_list($cat_id, $group_id);
    $html = '<table width="20%" id="attrTable">';
    $spec = 0;
    foreach ($attr AS $key => $val)
    {
        $html .= "<tr><td class='label'>";
        if ($val['attr_type'] == 1 || $val['attr_type'] == 2)
        {
            $html .= ($spec != $val['attr_id']) ?
                "<a href='javascript:;' onclick='addSpec(this)'>[+]</a>" :
                "<a href='javascript:;' onclick='removeSpec(this)'>[-]</a>";
            $spec = $val['attr_id'];
        }
		else
		{
			continue;
		}
        $html .= "$val[attr_name]</td><td><input type='hidden' name='attr_id_list[]' value='$val[attr_id]' />";
        if ($val['attr_input_type'] == 0)
        {
            $html .= '<input name="attr_value_list[]" type="text" value="' .htmlspecialchars($val['attr_value']). '" size="40" /> ';
        }
        elseif ($val['attr_input_type'] == 2)
        {
            $html .= '<textarea name="attr_value_list[]" rows="3" cols="40">' .htmlspecialchars($val['attr_value']). '</textarea>';
        }
        else
        {
            $html .= '<select name="attr_value_list[]">';
            $html .= '<option value="">' .$GLOBALS['_LANG']['select_please']. '</option>';
            $attr_values = explode("\n", $val['attr_values']);
            foreach ($attr_values AS $opt)
            {
                $opt    = trim(htmlspecialchars($opt));
                $html   .= ($val['attr_value'] != $opt) ?
                    '<option value="' . $opt . '">' . $opt . '</option>' :
                    '<option value="' . $opt . '" selected="selected">' . $opt . '</option>';
            }
            $html .= '</select> ';
        }
        $html .= ($val['attr_type'] == 1 || $val['attr_type'] == 2) ?
            $GLOBALS['_LANG']['spec_price'].' <input type="text" name="attr_price_list[]" value="' . $val['attr_price'] . '" size="5" maxlength="10" />':
            ' <input type="hidden" name="attr_price_list[]" value="0" />';
        $html .= '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}
function build_group_supp_html($parent_id,$group_id = 0)
{
  	$content = '<table width="20%" id="SuppTable">';
	$expand_supp = array();
    if ($group_id > 0)
	{	
	  $sql = "SELECT suppliers_id FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE group_id = '$group_id' ";	
      $expand_supp = $GLOBALS['db']->getCol($sql);
	}
	if ($parent_id > 0)
	{
     $psql = "SELECT suppliers_id, suppliers_name FROM " . 
	           $GLOBALS['ecs']->table("suppliers") . " WHERE parent_id='$parent_id'";
	 $res = $GLOBALS['db']->query($psql);
	 $i = 0;
	 while ($row = $GLOBALS['db']->fetchRow($res))
	 {
	   if (in_array($row['suppliers_id'],$expand_supp))
	   {
	     $selected = 'checked="checked"';
	   }	
	   else
	   {
	     $selected = '';
	   }
	   if ($i == 0)
	   {
	     $content .= '<tr><td class="label"><input type="checkbox" onclick="listTable.'."selectAll(this,'other_suppliers')".
	               '"/></td><td> 全选</td></tr>';
	   }
	   $i++;
	   $content .= "<tr><td class='label'>";
	   $content .= "<input type='checkbox' value='$row[suppliers_id]' name='other_suppliers[]' $selected></td>";
	   $content .= '<td>'.$row['suppliers_name'] . '</td></tr>';
	 }
	}
   $content .= '</table>';	
   return $content;
}
function create_basic_editor($input_name, $input_value = '')
{
    global $smarty;
    $editor = new FCKeditor($input_name);
    $editor->BasePath   = '../includes/fckeditor/';
    $editor->ToolbarSet = 'Mail';
    $editor->Width      = '50%';
    $editor->Height     = '300';
    $editor->Value      = $input_value;
    $FCKeditor = $editor->CreateHtml();
    $smarty->assign($input_name, $FCKeditor);
}
function handle_group_image($group_id, $image_files, $image_descs, $image_urls)
{
    foreach ($image_descs AS $key => $img_desc)
    {
        /* 是否成功上传 */
        $flag = false;
        if (isset($image_files['error']))
        {
            if ($image_files['error'][$key] == 0)
            {
                $flag = true;
            }
        }
        else
        {
            if ($image_files['tmp_name'][$key] != 'none')
            {
                $flag = true;
            }
        }
        if ($flag)
        {
            $upload = array(
                'name' => $image_files['name'][$key],
                'type' => $image_files['type'][$key],
                'tmp_name' => $image_files['tmp_name'][$key],
                'size' => $image_files['size'][$key],
            );
            if (isset($image_files['error']))
            {
                $upload['error'] = $image_files['error'][$key];
            }
            $img_original = $GLOBALS['image']->upload_image($upload);
            if ($img_original === false)
            {
                sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
            }
            $img_url = $img_original;
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('group_gallery') . " (group_id, img_url, img_desc) " .
                    "VALUES ('$group_id', '$img_url', '$img_desc')";
            $GLOBALS['db']->query($sql);
        }
        elseif (!empty($image_urls[$key]) && ($image_urls[$key] != $GLOBALS['_LANG']['img_file']) && ($image_urls[$key] != 'http://') && copy(trim($image_urls[$key]), ROOT_PATH . 'temp/' . basename($image_urls[$key])))
        {
            $image_url = trim($image_urls[$key]);
			$down_img = ROOT_PATH . 'temp/' . basename($image_url);
            /* 重新格式化图片名称 */
            $img_url = htmlspecialchars($image_url);
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('group_gallery') . " (group_id, img_url, img_desc) " .
                    "VALUES ('$group_id', '$img_url', '$img_desc')";
            $GLOBALS['db']->query($sql);
            @unlink($down_img);
        }
    }
}
function add_other_city($group_id, $city_list)
{
    /* 查询现有的扩展城市 */
    $sql = "SELECT city_id FROM " . $GLOBALS['ecs']->table('expand_city') .
            " WHERE group_id = '$group_id'";
    $exist_list = $GLOBALS['db']->getCol($sql);
    /* 删除不再有的城市 */
    $delete_list = array_diff($exist_list, $city_list);
    if ($delete_list)
    {
        $sql = "DELETE FROM " . $GLOBALS['ecs']->table('expand_city') .
                " WHERE group_id = '$group_id' " .
                "AND city_id " . db_create_in($delete_list);
        $GLOBALS['db']->query($sql);
    }
    /* 添加新加的城市 */
    $add_list = array_diff($city_list, $exist_list, array(0));
    foreach ($add_list AS $city_id)
    {
        // 插入记录
        $sql = "INSERT INTO " . $GLOBALS['ecs']->table('expand_city') .
                " (group_id, city_id) " .
                "VALUES ('$group_id', '$city_id')";
        $GLOBALS['db']->query($sql);
    }
}
function add_other_suppliers($group_id, $supp_list = array())
{
  
     $sql = "DELETE FROM " . $GLOBALS['ecs']->table('expand_suppliers') .
                " WHERE group_id = '$group_id' ";
     $GLOBALS['db']->query($sql);
    /* 添加新加的分类 */
	if (!empty($supp_list))
	{
      foreach ($supp_list AS $supp_id)
      {
        // 插入记录
        $sql = "INSERT INTO " . $GLOBALS['ecs']->table('expand_suppliers') .
                " (group_id, suppliers_id) " .
                "VALUES ('$group_id', '$supp_id')";
        $GLOBALS['db']->query($sql);
     }
   }
}
 function get_big_suppliers()
{
		$pArr = array();
	  	$psql = "SELECT suppliers_id, suppliers_name FROM " . 
	           $GLOBALS['ecs']->table("suppliers") . " WHERE parent_id='0' ORDER BY suppliers_id DESC";	
	    $pArr = $GLOBALS['db']->getAll($psql);
        return $pArr;
}	
function get_groupbuy_info($group_buy_id = 0)
{
    /* 取得团购活动信息 */
    $group_buy_id = intval($group_buy_id);
	$group_buy = array();
	if ($group_buy_id > 0)
	{
      $sql = "SELECT * , start_time AS start_date, end_time AS end_date " .
            "FROM " . $GLOBALS['ecs']->table('group_activity') ;
	  $sql .=  " WHERE group_id = '$group_buy_id'" ;
      $group_buy = $GLOBALS['db']->getRow($sql);
      /* 如果为空，返回空数组 */
      if (empty($group_buy))
     {
        return array();
      }
      $ext_info = unserialize($group_buy['ext_info']);
     $group_buy = array_merge($group_buy, $ext_info);
    /* 格式化时间 */
    $group_buy['formated_start_date'] = local_date('Y-m-d H:i', $group_buy['start_date']);
    $group_buy['formated_end_date'] = local_date('Y-m-d H:i', $group_buy['end_date']);
    $group_buy['formated_past_date'] = local_date('Y-m-d H:i', $group_buy['past_time']);
 
    /* 处理价格阶梯 */
    $price_ladder = $group_buy['price_ladder'];
    if (!is_array($price_ladder) || empty($price_ladder))
    {
        $price_ladder = array(array('amount' => 0, 'price' => 0));
    }
    else
    {
        foreach ($price_ladder as $key => $amount_price)
        {   
            $price_ladder[$key]['formated_price'] = $amount_price['price'];
        }
    }
    $group_buy['price_ladder'] = $price_ladder;
	$group_buy['group_price'] = $price_ladder[0]['formated_price'];
	$group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price'], 2, '.', '')*10;
	$group_buy['formated_lack_price'] = group_price_format($group_buy['market_price']- $group_buy['group_price']);
	$group_buy['formated_market_price']= group_price_format($group_buy['market_price']);
	$group_buy['formated_group_price']= group_price_format($group_buy['group_price']);
    $group_buy['formated_goods_rebate']= group_price_format($group_buy['goods_rebate']);
	$group_buy['endtime'] = local_date('Y,n-1,j',$group_buy['end_time']);
	$group_buy['url'] = rewrite_groupurl('team.php',array('id' => $group_buy['group_id']));
   }
    return $group_buy;
}
?>