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
	$sql = "SELECT a.group_id,a.group_name,a.goods_name,a.group_image,a.start_time,a.group_need,a.is_hdfk,a.already_orders,a.end_time,a.market_price,a.ext_info,a.city_id,a.upper_orders,a.lower_orders,a.group_desc,a.goods_comment,b.suppliers_name,b.suppliers_site,b.phone,b.address,b.suppliers_desc " .
            "FROM " . $GLOBALS['ecs']->table('group_activity')." AS a, ". $GLOBALS['ecs']->table('suppliers')." AS b ";
	$now = gmtime();
	$today = date('Y-m-d');
	$where = " WHERE a.start_time <= '$now' AND a.is_finished ='0' AND a.suppliers_id = b.suppliers_id ";
	$where .= 'AND a.activity_type=1 AND a.is_show = 1';
	$sql .= $where . " ORDER BY group_type ASC, start_time DESC, sort_order DESC";
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
		if ($group_buy['goods_type'] != 2){
			$cat_type_num = '服务';
		}else {
			$cat_type_num = '实物';
		}
		$orders_num = get_group_orders($group_buy['group_id'], $group_buy['group_need'],$group_buy['is_hdfk']);
		$group_buy['orders_num'] = $orders_num + $group_buy['already_orders'];
        $group_buy['price_ladder'] = $price_ladder;
	    $group_buy['group_price'] = $price_ladder[0]['formated_price'];
	    $group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price']*10, 1, '.', '');
		$group_buy['lack_price'] = $group_buy['market_price']- $group_buy['group_price'];
		$group_id = $group_buy['group_id'];
		$city_id = $group_buy['city_id'];
	    $sql = 'SELECT city_name,eng_name FROM ' . $GLOBALS['ecs']->table('group_city') . 
		       " WHERE city_id ='$city_id'";
		$city = $db->getRow($sql);		
		$group_url = rewrite_groupurl('team.php',array('id' => $group_buy['group_id']));
		$xml .= '<url>';
        $xml .= '<loc><![CDATA['.$weburl.$group_url.']]></loc>';
        $xml .= '<data>';
        $xml .= '<display>';
        $xml .= '<website>'.$_CFG['group_shopname'].'</website>';
        $xml .= '<identifier>' . $group_buy['group_id'] . '</identifier>';
        $xml .= '<default_url>'.$weburl.'</default_url>';
        $xml .= '<city>'. $group_buy['city_name'] .'</city>';
        $xml .= '<title><![CDATA['.$group_buy['group_name'].']]></title>';
        $xml .= '<short_title>'.$group_buy['goods_name'].'</short_title>';
        $xml .= '<description><![CDATA['.$group_buy['group_name'].']]></description>';
        $xml .= '<image>'.$weburl. $group_buy['group_image'] .'</image>';
        $xml .= '<tag>'.$group_buy['group_keywords'].'</tag>';
        $xml .= '<kinds>'.$cat_type_num.'</kinds>';
        $xml .= '<startTime>'. $group_buy['start_time'] .'</startTime>';
        $xml .=  '<endTime>'. $group_buy['end_time'] .'</endTime>';
        $xml .= '<value>'. $group_buy['market_price'] .'</value>';
        $xml .= '<price>'.$group_buy['group_price'].'</price>';
        $xml .= '<discount_amount>'.$group_buy['lack_price'].'</discount_amount>';
        $xml .= '<rebate>'.$group_buy['group_rebate'].'</rebate>';
        $xml .= '<bought>'. $group_buy['orders_num'] . '</bought>';
        $xml .= '<boughtmax>99999</boughtmax>';
        $xml .= '<soldOut>false</soldOut>';
        $xml .= '<merchantStartTime>'. $group_buy['start_time'] .'</merchantStartTime>';
        $xml .= '<merchantEndTime>'. $group_buy['past_time'] .'</merchantEndTime>';
		$xml .= '<tip><![CDATA['.strip_tags($group_buy['goods_comment']).']]></tip>';
        $xml .= '<shops>';
        $xml .= '<shop>';
        $xml .= '<name>'.$group_buy['suppliers_name'].'</name>';
        $xml .= '<tel>'.$group_buy['phone'].'</tel>';
        $xml .= '<addr>'.$group_buy['address'].'</addr>';
        $xml .= '<area>'.$group_buy['suppliers_site'].'</area>';
        $xml .= '</shop>';
        $xml .= '</shops></display>';
        $xml .= '<business>';
        $xml .= '<Name></Name>';
        $xml .= '<provides>';
        $xml .= '<provide>';
        $xml .= '<ProviderName></ProviderName>';
        $xml .= '<Address></Address>';
        $xml .= '</provide>';
        $xml .= '</provides>';
        $xml .= '<ProviderUrl></ProviderUrl>';
        $xml .= '<kinds></kinds>'; 
        $xml .= '<Contact></Contact>'; 
        $xml .= '<Telephone></Telephone>'; 
        $xml .= '<Map></Map>'; 
        $xml .= '</business>'; 
        $xml .= '</data>'; 
        $xml .= '</url>'; 
	}
	$xml .= '</urlset>';
echo $xml;
?>
