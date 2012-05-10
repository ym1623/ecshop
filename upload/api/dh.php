<?php 
/**
 * ECGROUPON 团购接口
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);

require('./init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');

header('Content-Type: application/xml; charset=' . EC_CHARSET);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Thu, 27 Mar 1975 07:38:00 GMT');
header('Last-Modified: ' . date('r'));
header('Pragma: no-cache');

	$sql = "SELECT group_id,group_name, group_image,start_time,end_time,market_price,ext_info,city_id,small_desc,already_orders,is_hdfk,group_need " .
            ",upper_orders,lower_orders FROM " . $GLOBALS['ecs']->table('group_activity') ;
	$now = gmtime();
	$where = " WHERE start_time <= '$now' AND is_finished ='0'";
	$where .= 'AND activity_type=1 AND is_show = 1';
	$sql .= $where . " ORDER BY group_type ASC, start_time DESC, sort_order DESC limit 1";
    $res = $GLOBALS['db']->query($sql);
	$weburl = $ecs->url();
	$weburl = str_replace('/api', '',$weburl);
    $xml = '<?xml version="1.0" encoding="utf-8"?><urlset>';
    while ($group_buy = $GLOBALS['db']->fetchRow($res))
	{
	     $ext_info = unserialize($group_buy['ext_info']);
         $group_buy = array_merge($group_buy, $ext_info);
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
		$orders_num = get_group_orders($group_buy['group_id'], $group_buy['group_need'],$group_buy['is_hdfk']);
		$group_buy['orders_num'] = $orders_num + $group_buy['already_orders'];
        $group_buy['price_ladder'] = $price_ladder;
	    $group_buy['group_price'] = $price_ladder[0]['formated_price'];
	    $group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price'], 2, '.', '');
		$group_buy['lack_price'] = $group_buy['market_price']- $group_buy['group_price'];
		if ($group_buy['city_id'] > 0)
		{
          $sql = 'SELECT city_name FROM' . $ecs->table('group_city') . " WHERE city_id='$group_buy[city_id]'";
		  $group_buy['city_name'] = $db->getOne($sql);
		}
		if ($group_buy['suppliers_id'] > 0)
		{
		  $sql = "SELECT suppliers_name FROM " . $ecs->table('suppliers') . " WHERE suppliers_id='$group_buy[suppliers_id]'";
		  $group_buy['suppliers_name'] = $db->getOne($sql); 
		}
		$group_url = rewrite_groupurl('team.php',array('id' => $group_buy['group_id']));
        $xml .= '<cityid></cityid>';
	    $xml .= '<cityname>'. $group_buy['city_name'] .'</cityname>';
		$xml .= '<goods>';
		$xml .= '<goods_title>' . $group_buy['group_name'] . '</goods_title>';
	    $xml .= '<goods_rules>' . $grou_buy['small_desc'] . '</goods_rules>';
        $xml .= '<goods_value>'. $group_buy['market_price'] .'</goods_value>';
        $xml .= '<goods_price>'.$group_buy['group_price'].'</goods_price>';
        $xml .= '<goods_rebate>'. $group_buy['group_rebate'].'</goods_rebate>';
		$xml .= '<goods_start_time>'. $group_buy['start_time'] .'</goods_start_time>';
        $xml .= '<goods_deadline>'. $group_buy['end_time'] .'</goods_deadline>';
		$xml .= '<goods_left_second></goods_left_second>';
		$xml .= '<goods_expire></goods_expire>';
		$xml .= '<goods_convey_fee></goods_convey_fee>';
		$xml .= '<goods_description></goods_description>';
		$xml .= '<goods_bought>'. $group_buy['orders_num'] . '</goods_bought>';
        $xml .= '<goods_min_bought>'. $group_buy['lower_orders'] . '</goods_min_bought>';
        $xml .= '<goods_max_bought>'. $group_buy['upper_orders'] . '</goods_max_bought>';
		$xml .= '<goods_sp_name>' .  $group_buy['suppliers_name'] . '</goods_sp_name>';
		$xml .= '<goods_sp_url>' . $weburl . $group_buy['group_image'] .'</goods_sp_url>';
        $xml .= '<goods_image_url>'. $group_buy['group_image'] .'</goods_image_url>';
        $xml .= '<goods_site_url>'. $weburl . $group_url .'</goods_site_url>';
        $xml .= '<goods_phone>' . $_CFG['group_phone'] . '</goods_phone></goods>';
 
	}
	$xml .= '</urlset>';
echo $xml;
?>

