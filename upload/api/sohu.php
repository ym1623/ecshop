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
	$sql = "SELECT a.group_id,a.group_name,a.group_image,a.start_time,a.group_need,a.is_hdfk,a.already_orders,a.end_time,a.market_price,a.ext_info,a.city_id,a.upper_orders,a.lower_orders,a.group_desc,b.suppliers_name,b.suppliers_site,b.phone,b.address,b.suppliers_desc " .
            "FROM " . $GLOBALS['ecs']->table('group_activity')." AS a, ". $GLOBALS['ecs']->table('suppliers')." AS b ";
	$now = gmtime();
	$today = date('Y-m-d');
	$where = " WHERE a.start_time <= '$now' AND a.is_finished ='0' AND a.suppliers_id = b.suppliers_id ";
	$where .= 'AND a.activity_type=1 AND a.is_show = 1';
	$sql .= $where . " ORDER BY group_type ASC, start_time DESC, sort_order DESC";
    $res = $GLOBALS['db']->query($sql);
	$weburl = $ecs->url();
   	$weburl = str_replace('/api', '',$weburl);

    $xml = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE ActivitySet SYSTEM "http://t123.sohu.com/api/ActivitySet3.0.dtd"><ActivitySet><Version>3.0</Version><Site>' . $_CFG['group_shopname'] . '</Site><SiteUrl>' . $weburl . '</SiteUrl><VerifyCode>iTxktwvl8hQX8Bqr1xr__zjhobzpa</VerifyCode><Update>'.$today.'</Update>';
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
	    $group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price']*10, 1, '.', '');
		$group_buy['lack_price'] = $group_buy['market_price']- $group_buy['group_price'];
	    //$group_buy['lack_price'] = group_price_format($group_buy['market_price']- $group_buy['group_price']);
	    //$group_buy['market_price']= group_price_format($group_buy['market_price']);
	    //$group_buy['group_price']= group_price_format($group_buy['group_price']);
		$group_id = $group_buy['group_id'];
		$city_id = $group_buy['city_id'];
	    $sql = 'SELECT gc.city_id,gc.city_name,gc.eng_name FROM ' . $GLOBALS['ecs']->table('expand_city') . 
		       " AS ex," . $ecs->table('group_city') . " AS gc" . 
		       " WHERE gc.city_id = ex.city_id AND ex.group_id ='$group_id'  ORDER BY gc.city_sort DESC";
		
        $cityall = $GLOBALS['db']->getAll($sql);
		//print_r($cityall);
		if ($group_buy['city_id'] > 0)
		{
          $sql = 'SELECT city_id,city_name,eng_name FROM' . $ecs->table('group_city') . " WHERE city_id='$group_buy[city_id]'";
		  $cityall[] = $db->getRow($sql);
		}
		foreach($cityall as $city)
		{
		$group_url = 'jump.php?id=' . $group_buy['group_id'] . "&s=sohu&c=".$city['eng_name'];
        $xml .= '<Activity>';
		$xml .= '<Title>' . $group_buy['group_name'] . '</Title>';
        $xml .= '<Url><![CDATA['.$weburl.$group_url.']]></Url>';
		$xml .= '<Description><![CDATA['.$group_buy['group_desc'].']]></Description>';
        $xml .= '<ImageUrl>'.$weburl. $city['eng_name'] .'</ImageUrl>';
        $xml .= '<CityName>'. $city['city_name'] .'</CityName>';
		$xml .= '<AreaCode></AreaCode>';
		$xml .= '<Value>'. $group_buy['market_price'] .'</Value>';
        $xml .= '<Price>'.$group_buy['group_price'].'</Price>';
        $xml .= '<ReBate>'. $group_buy['group_rebate'].'</ReBate>';
        $start_time=local_date("YmdGis", $group_buy['start_time']);
		$end_time=local_date("YmdGis", $group_buy['end_time']);
		$xml .= '<StartTime>'. $start_time ."0".'</StartTime>';
        $xml .= '<EndTime>'. $end_time ."0".'</EndTime>';
        $xml .= '<ExpireDate>'.local_date("Y-m-d",$group_buy['past_time']).'</ExpireDate>';
		$xml .= '<Quantity>0</Quantity>';
        $xml .= '<Bought>'. $group_buy['orders_num'] . '</Bought>';
        $xml .= '<MinBought>'. $group_buy['lower_orders'] . '</MinBought>';
        $xml .= '<BoughtLimit>0</BoughtLimit>';
		$xml .= '<Goods>';
		$xml .= '<Name>' . $group_buy['group_name'] . '</Name>';
		$xml .= '<ProviderName>' . $group_buy['suppliers_name'] . '</ProviderName>';
		$xml .= '<ProviderUrl>' . $weburl . '</ProviderUrl>';
		$xml .= '<ImageUrlSet>' . $weburl . $group_buy['group_image'] . '</ImageUrlSet>';
		$xml .= '<Contact></Contact>';
		$xml .= '<Telephone>'.$group_buy['phone'].'</Telephone>';
		$xml .= '<Address>' . $group_buy['address'] . '</Address>';
        $xml .= '<Map></Map><GoodDescription><![CDATA['.$group_buy['suppliers_desc'].']]></GoodDescription></Goods></Activity>';
		}
	}
	$xml .= '</ActivitySet>';
echo $xml;
?>
