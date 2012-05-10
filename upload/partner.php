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
$cat_id = isset($_REQUEST['catid']) && intval($_REQUEST['catid']) > 0 ? intval($_REQUEST['catid']) : 0;
//$city_id = isset($_COOKIE['ECS']['cityid']) && intval($_COOKIE['ECS']['cityid']) > 0 ? intval($_COOKIE['ECS']['cityid']) : $_CFG['group_city'];
    $city_id  = (isset($_REQUEST['cityid']) && intval($_REQUEST['cityid']) > 0) ? intval($_REQUEST['cityid'])   : (isset($_COOKIE['ECS']['cityid']) ? $_COOKIE['ECS']['cityid'] :  $_CFG['group_city']);
    setcookie('ECS[cityid]', $city_id, gmtime() + 86400 * 7);
$cache_id = $_CFG['lang'] . '-' . $size . '-' . $page . '-' . $city_id . '-' . $cat_id;
$cache_id = sprintf('%X', crc32($cache_id));

    /* 如果没有缓存，生成缓存 */
if (!$smarty->is_cached('group_partner.dwt', $cache_id))
{
	    $count = get_suppliers_count($city_id,$cat_id);
        
		if ($count > 0)
        {
            /* 取得当前页的团购活动 */
            $suppliers_list = get_suppliers_list($city_id,$cat_id, $size, $page);
            $smarty->assign('suppliers_list',  $suppliers_list);
            /* 设置分页链接 */
            $pager = get_group_pager('partner.php', array('cityid' => $city_id), $count, $page, $size);
            $smarty->assign('pager', $pager);
        }
        /*else
		{
		    $url = rewrite_groupurl('subscribe.php');
            ecs_header("Location: $url\n");
            exit; 
		}*/
        /* 模板赋值 */
		assign_public($city_id);
		$smarty->assign('where', 'partner');
		$show_groupclass = !empty($_CFG['show_groupclass']) ? explode(',',$_CFG['show_groupclass']) : array();
	    if (in_array(3,$show_groupclass))
	    {
		 $smarty->assign('catid',  $cat_id);
		 $smarty->assign('class_url',  rewrite_groupurl('partner.php'));
		 $smarty->assign('class_list', group_class_list(2,true,'partner.php'));
		}
		//$smarty->assign('today_group',  get_today_grouplist('0',$city_id, $cat_id));

}
$smarty->display('group_partner.dwt', $cache_id);

function get_suppliers_count($city_id = 0 , $cat_id = 0)
{
    $now = gmtime();
    $sql = "SELECT COUNT(*) " .  "FROM " . $GLOBALS['ecs']->table('suppliers');
	$where =  " WHERE is_check = 1 AND is_show =1 AND parent_id = 0";	

	if ($city_id > 0)
	{
	  $where .= " AND city_id='$city_id'";
	}		
    if ($cat_id > 0)
	{
	  $where .= " AND cid='$cat_id'";
	}		
    $sql .= $where;

    return $GLOBALS['db']->getOne($sql);
}

function get_suppliers_list($city_id = 0 , $cat_id = 0,$size, $page)
{
    /* 取得团购活动 */
    $suppliers_list = array();
    $sql = "SELECT suppliers_id, suppliers_name,phone,linkman,is_check,is_show,spread_img FROM " .
		        $GLOBALS['ecs']->table("suppliers");
	$where = " WHERE is_check = 1 AND is_show =1 AND parent_id = 0";		
	if ($city_id > 0)
	{
	  $where .= " AND city_id='$city_id'";
	}		
    if ($cat_id > 0)
	{
	  $where .= " AND cid='$cat_id'";
	}		
    $sql .= $where . " ORDER BY suppliers_id DESC";
    $suppres = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);
    while ($row = $GLOBALS['db']->fetchRow($suppres))
    {
		$suppliers_id = $row['suppliers_id'];
		$row['group_num'] = 0;
		$now = gmtime();
		$row['save_amount'] = 0;
		$row['person_num'] = 0;
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
			$row['group_num'] += 1;
		    $row['save_amount'] +=  $group_buy['rebate_price'] * $order['person_num'];
			$row['person_num'] += $order['person_num'];
		 }		
	    $row['formated_save_amount']= group_price_format($row['save_amount']);
		$row['url'] = rewrite_groupurl('brander.php',array('id'=>$row['suppliers_id']));
        $suppliers_list[] = $row;
    }
    return $suppliers_list;
}

?>