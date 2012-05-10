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
$publishdate = date('Y-m-d');

	$sql = "SELECT a.group_id,a.group_name,a.group_image,a.start_time,a.group_need,a.goods_type,a.group_type,a.cat_id,a.is_hdfk,a.already_orders,a.end_time,a.past_time,a.market_price,a.ext_info,a.group_keywords,a.small_desc ,a.goods_comment,a.city_id,b.suppliers_name,b.suppliers_site,b.west_way,b.east_way,b.phone,b.address " .
            "FROM " . $GLOBALS['ecs']->table('group_activity')." AS a, ". $GLOBALS['ecs']->table('suppliers')." AS b ";
	$now = gmtime();
	$where = " WHERE start_time <= '$now' AND is_finished ='0'";
		$where .= 'AND a.activity_type=1 AND a.is_show = 1';
	$sql .= $where . " ORDER BY group_type ASC, start_time DESC, sort_order DESC";
    $res = $GLOBALS['db']->query($sql);
	$weburl = $ecs->url();
    $weburl = str_replace('/api', '',$weburl);

    $xml = '<?xml version="1.0" encoding="utf-8"?>'.
	      '<sdd><provider>' . $_CFG['group_shopname'] .
		   '</provider><version>1.0</version><dataServiceId>1_1</dataServiceId>';
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
		$cat_type_num='';
		$orders_num = get_group_orders($group_buy['group_id'], $group_buy['group_need'],$group_buy['is_hdfk']);
		$group_buy['orders_num'] = $orders_num + $group_buy['already_orders'];
        $group_buy['price_ladder'] = $price_ladder;
	    $group_buy['group_price'] = $price_ladder[0]['formated_price'];
	    $group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price']*10, 1, '.', '');
		$group_buy['lack_price'] = $group_buy['market_price']- $group_buy['group_price'];
		$sql = 'SELECT city_name,eng_name FROM ' . $GLOBALS['ecs']->table('group_city') . 
		       " WHERE city_id ='$city_id'";
		$city = $db->getRow($sql);		
		$group_url = rewrite_groupurl('team.php',array('id' => $group_buy['group_id']));
        $xml .= '<meta>';
		$xml .= '<description></description>';
		$xml .= '<fields>';
		$xml .= '<field><name>imageaddress1</name><description>'.$weburl. $group_buy['group_image'] .'</description></field>';
        $xml .= '<field><name>imagealt1</name><description><![CDATA['.$group_buy['group_name'].']]></description></field>';
        $xml .= '<field><name>imagelink1</name><description><![CDATA['.$weburl.$group_url.']]></description></field>';
        $xml .= '<field><name>content1</name><description><![CDATA['.$group_buy['group_name'].']]></description></field>';
        $xml .= '<field><name>linktext1</name><description><![CDATA['.$group_buy['small_desc'].']]></description></field>';
        $xml .= '<field><name>linktarget1</name><description>![CDATA['.$weburl.$group_url.']]></description></field>';
        $xml .= '<field><name>content2</name><description>'. $group_buy['market_price'] .'</description></field>';
        $xml .= '<field><name>content3</name><description>'.$group_buy['group_price'].'</description></field>';
        $xml .= '<field><name>content4</name><description>'.$group_buy['group_rebate'].'</description></field>';
        $xml .= '<field><name>content5</name><description>'.$cat_type_num.'</description></field>';
        $xml .= '<field><name>content6</name><description>'. $city['city_name'] .'</description></field>';
        $xml .= '<field><name>content7</name><description>'.$group_buy['orders_num'].'</description></field>';
        $xml .= '<field><name>content10</name><description>'.$group_buy['suppliers_name'].'</description></field>';
        $xml .= '<field><name>content11</name><description>成功</description></field>';
        $xml .= '<field><name>content12</name><description>'.$group_buy['past_time'].'</description></field>';
        $xml .= '<field><name>content13</name><description></description></field>';
		$xml .= '<field><name>content14</name><description>'.$group_buy['address'].'</description></field>';
		$xml .= '<field><name>content15</name><description>'.$group_buy['phone'].'</description></field>';
		$xml .= '<field><name>linktext2</name><description>'.$_CFG['group_shopname'].'</description></field>';
		$xml .= '<field><name>linktarget2</name><description>'.$weburl. $city['eng_name'] .'</description></field>';
		$xml .= '<field><name>content8</name><description>开始时间</description><type>'. $group_buy['start_time'] .
		        '</type><format>'.local_date("Y-m-d H:i:s",$group_buy['start_time']).'</format></field>';
		$xml .= '<field><name>content9</name><description>结束时间</description><type>'.$group_buy['end_time'].
		      '</type><format>'.local_date("Y-m-d H:i:s",$group_buy['end_time']).'</format></field>';
		$xml .= '</fields></meta>';
		$xml .= '<updatemethod>all</updatemethod>';
		$xml .= '<creator>' . $weburl . '</creator>';
	    $xml .= '<title>' . $group_buy['group_name'] . '</title>';
		$xml .= '<publishdate>'.$publishdate.'</publishdate>';
        $xml .= '<imageaddress1>' . $weburl . $group_buy['group_image'] . '</imageaddress1>';
	    $xml .= '<imagealt1>' . $group_buy['group_name'] . '</imagealt1>';
        $xml .= '<imagelink1>' . $weburl . $group_buy['group_image'] . '</imagelink1>';
	    $xml .= '<content1>' . $group_buy['group_name'] . '</content1>';
	    $xml .= '<linktext1>' . $group_buy['group_name'] . '</linktext1>';
        $xml .= '<linktarget1>'. $weburl . $group_url .'</linktarget1>';
		$xml .= '<content2>'. $group_buy['market_price'] .'</content2>';
        $xml .= '<content3>'.$group_buy['group_price'].'</content3>';
        $xml .= '<content4>'. $group_buy['group_rebate'].'</content4>';
		$sql = 'SELECT cat_name  FROM' . $ecs->table('category') . " WHERE cat_id='$group_buy[cat_id]'";
		$cat_name = $db->getOne($sql);
		$xml .= '<content5>'.$cat_name.'</content5>';
        $xml .= '<content6>' . $group_buy['city_name'] . '</content6>';
	    $xml .= '<linktext2>'. $_CFG['group_shopname'] . '</linktext2>';
	    $xml .= '<linktarget2>'.$weburl.'</linktarget2>';
		$xml .= '<content7>'. $group_buy['orders_num'] . '</content7>';
		$xml .= '<content10>'.$_CFG['group_shopname'].'</content10>';
		if($group_buy['is_finished'] ==0 )
		{
		$content11="进行";
		}elseif($group_buy['is_finished'] ==2)
		{
		$content11="成功";
		}
		$xml .= '<content11>'.$content11.'</content11>';
		$xml .= '<content12></content12>';
        $xml .= '<content13>'. $group_buy['lower_orders'] . '</content13>';
		$xml .= '<content14></content14>';
		$xml .= '<content15>' . $_CFG['group_phone'] . '</content15>';
		$start_time=local_date("Y-m-d G:i:s", $group_buy['start_time']);
		$end_time=local_date("Y-m-d G:i:s", $group_buy['end_time']);
		$xml .= '<content8>'. $start_time .'</content8>';
        $xml .= '<content9>'. $end_time .'</content9>';
		$xml .= '<valid>1</valid></meta>';
	}
	$xml .= '</sdd>';
echo $xml;
?>
