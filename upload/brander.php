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
$smarty->template_dir   = ROOT_PATH . 'template/' . $_CFG['formwork'];

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$size = 16;
$page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
$sid = isset($_REQUEST['id']) && intval($_REQUEST['id']) > 0 ? intval($_REQUEST['id']) : 0;
$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];

$cache_id = $_CFG['lang'] . '-' . $size . '-' . $page . '-' . $city_id . '-' . $sid;
$cache_id = sprintf('%X', crc32($cache_id));

    /* 如果没有缓存，生成缓存 */
if (!$smarty->is_cached('group_brander.dwt', $cache_id))
{
	    $suppliers = get_suppliers_info($sid);
		if (empty($suppliers))
		{
		    $url = rewrite_groupurl('partner.php');
            ecs_header("Location: $url\n");
            exit; 
		}
	    $count = group_suppliers_count($sid);
		if ($count > 0)
        {
            $group_suppliers = group_suppliers_list($sid, $size, $page);
            $smarty->assign('group_suppliers', $group_suppliers);
            $pager = get_group_pager('brander.php', array('id'=>$sid), $count, $page, $size);
            $smarty->assign('pager', $pager);
        }
       $smarty->assign('suppliers',  $suppliers);
        /* 模板赋值 */
	   assign_public($city_id);
	   $smarty->assign('where', 'partner');

}
$smarty->display('group_brander.dwt', $cache_id);


function group_suppliers_count($suppliers_id)
{
	$supp_count = 0;
    if($suppliers_id > 0)
	{
	  $now = gmtime();	
      $sql = "SELECT COUNT(*) " .  "FROM " . $GLOBALS['ecs']->table('group_activity') . " AS ga" .
            " WHERE ga.suppliers_id = '$suppliers_id'".
			" AND ((ga.activity_type = '1' AND ga.start_time <= '$now') OR ga.activity_type != '1')";		
      $supp_count = $GLOBALS['db']->getOne($sql);
    }
	return $supp_count;
}

function group_suppliers_list($suppliers_id = 0,$size, $page)
{
    /* 取得团购活动 */
    $group_list = array();
	$now = gmtime();
    $sql = "SELECT group_id,group_name,group_image,group_need,ext_info,market_price,is_finished,start_time,already_orders " .
            ",is_hdfk FROM " . $GLOBALS['ecs']->table('group_activity').
            " AS ga WHERE ga.suppliers_id = '$suppliers_id'".
			" AND ((ga.activity_type = '1' AND ga.start_time <= '$now') OR ga.activity_type != '1')".		
            " ORDER BY ga.start_time DESC,ga.is_finished ASC ";
    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($group_buy = $GLOBALS['db']->fetchRow($res))
    {
        $ext_info = unserialize($group_buy['ext_info']);
        $group_buy = array_merge($group_buy, $ext_info);
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
                $price_ladder[$key]['formated_price'] = group_price_format($amount_price['price']);
            }
        }
		$orders_num = get_group_orders($group_buy['group_id'], $group_buy['group_need'],$group_buy['is_hdfk']);
		$group_buy['orders_num'] = $orders_num + $group_buy['already_orders'];
        $group_buy['price_ladder'] = $price_ladder;
		$group_buy['formated_start_date'] = local_date('Y年m月d日', $group_buy['start_time']);
        $group_buy['formated_cur_price'] = $price_ladder[0]['formated_price'];
        /* 处理链接 */
		$group_buy['rebate_price'] = $group_buy['market_price'] - $price_ladder[0]['price'];
		$group_buy['formated_market_price'] = group_price_format($group_buy['market_price'], false);
		$group_buy['formated_rebate_price'] = group_price_format($group_buy['rebate_price'], false);
		$group_buy['group_image'] = get_image_path('0', $group_buy['group_image'], true);
		$group_buy['rebate'] = number_format($price_ladder[0]['price'] / $group_buy['market_price'], 2, '.', '') * 10;
        /* 加入数组 */
		$group_buy['all_rebate'] = $group_buy['rebate_price'] * $group_buy['orders_num'];
		$group_buy['formated_all_rebate'] = group_price_format($group_buy['all_rebate'], false);
		$group_buy['url'] = rewrite_groupurl('team.php',array('id'=>$group_buy['group_id']));
        $group_list[] = $group_buy;
    }
    return $group_list;
}

function get_suppliers_info($suppliers_id = 0)
{
    /* 取得团购活动 */
    $suppliers_arr = array();
    $sql = "SELECT * FROM " .
		   $GLOBALS['ecs']->table("suppliers").
	       " WHERE is_check = 1 AND is_show =1 AND suppliers_id='$suppliers_id'";		
    $suppliers_arr = $GLOBALS['db']->getRow($sql);
    if($suppliers_arr)
    {   
	    $now = gmtime();
		$suppliers_id = $suppliers_arr['suppliers_id'];
		$suppliers_arr['group_num'] = 0;
		$suppliers_arr['person_num'] = 0;
		$save_amount = 0;
	    $where = " AND ((ga.activity_type = '1' AND ga.start_time <= '$now' ) OR ga.activity_type != '1')";
		$sql = "SELECT SUM(g.goods_number) + ga.already_orders AS person_num,ga.ext_info,ga.market_price FROM " .
		        $GLOBALS['ecs']->table('order_info') . " AS o," .
                $GLOBALS['ecs']->table('order_goods') . " AS g," .
			    $GLOBALS['ecs']->table('group_activity') . " AS ga" .
                " WHERE o.order_id = g.order_id " .
                "AND o.extension_code = 'group_buy' " .
                "AND o.extension_id = ga.group_id " .
			    "AND ga.suppliers_id = '$suppliers_id'". $where .
         	    " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
                " AND ( o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
			    " OR o.pay_id " . db_create_in(hdfk_payment_id()) . ") " .' GROUP BY ga.group_id';
        $res = $GLOBALS['db']->query($sql);
        while ($order = $GLOBALS['db']->fetchRow($res))
		{
			$group_buy = unserialize($order['ext_info']);
            $price_ladder = $group_buy['price_ladder'];
            if (!is_array($price_ladder) || empty($price_ladder))
            {
              $price_ladder = array(array('amount' => 0, 'price' => 0));
            }
            else
            {
              foreach ($price_ladder as $key => $amount_price)
              {
                $price_ladder[$key]['formated_price'] = group_price_format($amount_price['price']);
              }
            }
		    $group_buy['rebate_price'] = $order['market_price'] - $price_ladder[0]['price'];
			$suppliers_arr['group_num'] += 1;
		    $save_amount +=  $group_buy['rebate_price'] * $order['person_num'];
			$suppliers_arr['person_num'] += $order['person_num'];
		 }
	    $suppliers_arr['formated_save_amount']= group_price_format($save_amount);
    }
    return $suppliers_arr;
}

?>