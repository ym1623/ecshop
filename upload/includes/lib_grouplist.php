<?php

/**
 * ECGROUPON 提交用户评论
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/


function group_buy_count($city_id = 0 , $cat_id = 0 ,$act_type = 1)
{
    $sql = "SELECT COUNT(*) " .  "FROM " . $GLOBALS['ecs']->table('group_activity');
	if($act_type == 1)
	{
	  $now = gmtime();
	  $where = " WHERE start_time <= '$now' AND activity_type = '$act_type'";		
	}
	else
	{
	  $where = " WHERE activity_type = '$act_type'";
	}
	if ($city_id > 0)
	{
	  $where .= " AND (city_id='$city_id' OR " . get_expand_city($city_id) . ")";
	}		
    if ($cat_id > 0)
	{
	  $where .= " AND cat_id='$cat_id'";
	}
	$where .= " AND is_show = 1";
    $sql .= $where;
    return $GLOBALS['db']->getOne($sql);
}

function group_buy_list($city_id = 0 , $cat_id = 0, $act_type = 1, $size, $page)
{
    /* 取得团购活动 */
    $group_list = array();
    $sql = "SELECT group_id,group_name,group_image,group_need,ext_info,market_price,is_hdfk," .
            "activity_type,is_finished,start_time,end_time,already_orders FROM " . $GLOBALS['ecs']->table('group_activity') ;
	if($act_type == 1)
	{
	  $now = gmtime();
	  $where = " WHERE start_time <= '$now' AND activity_type = '$act_type'";		
	}
	else
	{
	  $where = " WHERE activity_type = '$act_type'";
	}
	if ($city_id > 0)
	{
	  $where .= " AND (city_id='$city_id' OR " . get_expand_city($city_id) . ")";
	}		
    if ($cat_id > 0)
	{
	  $where .= " AND cat_id='$cat_id'";
	}
	$where .= " AND is_show = 1";
    $sql .= $where . " ORDER BY start_time DESC,is_finished ASC,sort_order DESC";
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
		if ($group_buy['activity_type'] == 2)
		{
		 $group_buy['formated_start_date'] = local_date('Y年m月d日 H:i:s', $group_buy['start_time']);
		 $group_buy['formated_end_date'] = local_date('Y年m月d日 H:i:s', $group_buy['end_time']);
		}
		else
		{
		  	 $group_buy['formated_start_date'] = local_date('Y年m月d日', $group_buy['start_time']);
		     $group_buy['formated_end_date'] = local_date('Y年m月d日', $group_buy['end_time']);
		}
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


?>