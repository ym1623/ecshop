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
	$where = " WHERE a.start_time <= '$now' AND a.is_finished ='0' AND a.suppliers_id = b.suppliers_id ";
	$where .= 'AND a.activity_type=1 AND a.is_show = 1';
	$sql .= $where . " ORDER BY group_type ASC, start_time DESC, sort_order DESC";
    $res = $GLOBALS['db']->query($sql);
	$weburl = $ecs->url();
   	$weburl = str_replace('/api', '',$weburl);
		$xml = '<?xml version="1.0" encoding="utf-8"?><urlset>';
		$xml .= '<website>';
        $xml .= '<loc>';
		$xml .= '<mainurl><![CDATA['.$weburl.']]></mainurl>';
		$xml .= '<submainurls>';
		$xml .= '<submainurl></submainurl>';
		$xml .= '</submainurls>';
        $xml .= '</loc>';
        $xml .= '<data>';
        $xml .= '<website_name>'.$_CFG['group_shopname'].'</website_name>';
        $xml .= '<address></address>';
        $xml .= '<contact_name></contact_name>';
        $xml .= '<tel></tel>';
        $xml .= '<mail></mail>';
        $xml .= '<msn></msn>';
        $xml .= '<qq></qq>';
        $xml .= '<tuannum>3</tuannum>';
        $xml .= '<back></back>';
        $xml .= '<return>true</return>';
        $xml .= '<aptitudes>';
        $xml .= '<aptitude></aptitude>';
        $xml .= '</aptitudes>';
        $xml .= '<startTime></startTime>';
        $xml .= '</data>';
        $xml .= '</website>';
        $xml .= '</urlset>';
echo $xml;
?>
