<?php

/**
 * ECGROUPON 提交用户评论
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/


function get_group_buy_info($group_buy_id = 0, $city_id = 0,$cat_id = 0)
{
    /* 取得团购活动信息 */
    $group_buy_id = intval($group_buy_id);
    $sql = "SELECT * , start_time AS start_date, end_time AS end_date " .
            "FROM " . $GLOBALS['ecs']->table('group_activity') ;
	$now = gmtime();		
	if ($group_buy_id <= 0)
    {
		$sql .= " WHERE  start_time <= '$now' AND is_finished ='0' AND activity_type = 1" ;		
	   	if ($city_id > 0)
	   {
	     $sql .= " AND (city_id='$city_id' OR " . get_expand_city($city_id) . ")";
	   }		
       if ($cat_id > 0)
	   {
	    $sql .= " AND cat_id='$cat_id'";
	   }
	   $sql .= " AND is_show = 1";
	   $sql .= " ORDER BY group_type ASC,start_time DESC,sort_order DESC LIMIT 1";		
			
    } else {
		   
	   $sql .=  " WHERE group_id = '$group_buy_id' AND is_show = 1" ;
	}
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
	$group_buy['endtime'] = local_date('Y,n-1,j,H,i,s',$group_buy['end_time']);
	$group_buy['url'] = rewrite_groupurl('team.php',array('id' => $group_buy['group_id']));
    return $group_buy;
}

function get_today_grouplist($group_id = 0, $city_id = 0 , $cat_id = 0, $act_type = 1)
{
    /* 取得团购活动信息 */
	$sql = "SELECT group_id,group_name, group_image,ext_info,market_price " .
            "FROM " . $GLOBALS['ecs']->table('group_activity');
			
	if($act_type == 1)
	{
	  $now = gmtime();
	  $where = " WHERE activity_type = '$act_type' AND start_time <= '$now' AND is_finished ='0' AND group_id <> '$group_id'";
	}
	else
	{
	  $where = " WHERE activity_type = '$act_type' AND is_finished ='0' AND group_id <> '$group_id'";
	}

	if ($city_id > 0)
	{
	   $where .= " AND (city_id='$city_id' OR " . get_expand_city($city_id) . ")";
	}		
    if ($cat_id > 0)
	{
	  $where .= " AND cat_id='$cat_id'";
	}
	$where .= " AND is_show='1'"; 
	$sql .= $where . " ORDER BY start_time DESC, group_type ASC, sort_order DESC";
	if ($GLOBALS['_CFG']['left_group_num'] > 0)
	{
	  $sql .= " LIMIT " . $GLOBALS['_CFG']['left_group_num'];
	}
    $res = $GLOBALS['db']->query($sql);
	$group_buy = array();
	while ($row = $GLOBALS['db']->fetchRow($res))
	{ 
	    $ext_info = unserialize($row['ext_info']);
         $row = array_merge($row, $ext_info);
           /* 处理价格阶梯 */
         $price_ladder = $row['price_ladder'];
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
        $row['price_ladder'] = $price_ladder;
	    $row['group_price'] = $price_ladder[0]['formated_price'];
	    $row['group_rebate'] = number_format($price_ladder[0]['price']/$row['market_price'], 2, '.', '')*10;
	    $row['formated_lack_price'] = group_price_format($row['market_price']- $row['group_price']);
	    $row['formated_market_price'] = group_price_format($row['market_price']);
	    $row['formated_group_price'] = group_price_format($row['group_price']);
	    $row['url'] = rewrite_groupurl('team.php',array('id' => $row['group_id']));
		$group_buy[] = $row;
	}
    return $group_buy;
}
function get_more_grouplist($city_id = 0 ,$cat_id = 0, $act_type = 1)
{
    /* 取得团购活动信息 */
	$sql = "SELECT group_id,group_name, group_image,start_time,end_time,goods_rebate " .
            "FROM " . $GLOBALS['ecs']->table('group_activity') ;
	if($act_type == 1)
	{
	  $now = gmtime();
	  $where = " WHERE activity_type = '$act_type' AND start_time <= '$now' AND is_finished ='0'";
	}
	else
	{
	  $where = " WHERE activity_type = '$act_type' AND is_finished ='0'";
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
	$sql .= $where . " ORDER BY start_time DESC, group_type ASC,sort_order DESC";
	/*if ($limitnum > 0)
	{
	  $sql .= " LIMIT " . $limitnum;
	}*/
    $res = $GLOBALS['db']->query($sql);
	$group_buy = array();
	$i=1;
	//$now = gmtime();
	while ($row = $GLOBALS['db']->fetchRow($res))
	{ 
	    $row['url'] = rewrite_groupurl('team.php',array('id' => $row['group_id']));
		$row['invite_url'] = rewrite_groupurl('invite.php',array('id' => $row['group_id']));
		$row['endtime'] = local_date('Y,n-1,j,H,i,s',$row['end_time']);
		$row['formated_goods_rebate']= group_price_format($row['goods_rebate']);
		$group_buy[$i] = $row;
		$i++;
	}
    return $group_buy;
}

/*
 * 取得某团购活动统计信息
 * @param   int     $group_buy_id   团购活动id
 * @param   float   $deposit        保证金
 * @return  array   统计信息
 *                  total_order     总订单数
 *                  total_goods     总商品数
 *                  valid_order     有效订单数
 *                  valid_goods     有效商品数
 */
function get_group_buy_stat($group_buy_id)
{
    $group_buy_id = intval($group_buy_id);
   
    /* 取得总订单数和总商品数 */
	$all_stat = array();
	$actual_stat = array();
    $sql = "SELECT COUNT(*) AS total_order, SUM(g.goods_number) AS total_goods " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
                $GLOBALS['ecs']->table('order_goods') . " AS g " .
            " WHERE o.order_id = g.order_id " .
            "AND o.extension_code = 'group_buy' " .
            "AND o.extension_id = '$group_buy_id' " .
            "AND g.goods_id = '$group_buy_id' ";
            //"AND (order_status = '" . OS_CONFIRMED . "' OR order_status = '" . OS_UNCONFIRMED . "')";
    $all_stat = $GLOBALS['db']->getRow($sql);
	
	if ($all_stat['total_order'] == 0)
    {
        $all_stat['total_goods'] = 0;
    }
    $sql = "SELECT COUNT(*) AS ss_order, SUM(g.goods_number) AS ss_goods " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
                $GLOBALS['ecs']->table('order_goods') . " AS g " .
            " WHERE o.order_id = g.order_id " .
            "AND o.extension_code = 'group_buy' " .
            "AND o.extension_id = '$group_buy_id' " .
            "AND g.goods_id = '$group_buy_id' ".
			" AND o.shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING,SS_SHIPPED_ING)).
            " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
    $ss_stat = $GLOBALS['db']->getRow($sql);
	if ($ss_stat['ss_order'] == 0)
    {
        $ss_stat['ss_goods'] = 0;
    }
   	$all_stat = array_merge($all_stat, $ss_stat);		
	$sql = "SELECT COUNT(*) AS actual_order, SUM(g.goods_number) AS actual_goods,SUM(o.bonus) AS actual_bonus,SUM(o.surplus) AS actual_surplus," .
	        "SUM(o.money_paid) AS actual_money,SUM(o.bonus+o.surplus+o.money_paid) AS actual_amount ".
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
                $GLOBALS['ecs']->table('order_goods') . " AS g " .
            " WHERE o.order_id = g.order_id " .
            "AND o.extension_code = 'group_buy' " .
            "AND o.extension_id = '$group_buy_id' " .
            "AND g.goods_id = '$group_buy_id' " .
            " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
            " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
    $actual_stat = $GLOBALS['db']->getRow($sql);
    if ($actual_stat['actual_order'] == 0)
    {
        $actual_stat['actual_goods'] = 0;
		$actual_stat['actual_surplus'] = 0;
		$actual_stat['actual_money'] = 0;
		$actual_stat['actual_bonus'] = 0;
		$actual_stat['actual_amount'] = 0;
    }
    $actual_stat['formated_actual_surplus'] = group_price_format($actual_stat['actual_surplus']);
	$actual_stat['formated_actual_money'] = group_price_format($actual_stat['actual_money']);
	$actual_stat['formated_actual_bonus'] = group_price_format($actual_stat['actual_bonus']);
	$actual_stat['formated_actual_amount'] = group_price_format($actual_stat['actual_amount']);
	$all_stat = array_merge($all_stat, $actual_stat);		
    return $all_stat;
}
/**
 * 获得团购的状态
 *
 * @access  public
 * @param   array
 * @return  integer
 */
function get_group_buy_status($group_buy)
{
    $now = gmtime();
    if ($group_buy['is_finished'] == 0)
    {
        /* 未处理 */
        if ($now < $group_buy['start_time'])
        {
            $status = GBS_PRE_START;
        }
        elseif ($now > $group_buy['end_time'])
        {   
            $status = GBS_FINISHED;
        }
        else
        {
		   if ($group_buy['succeed_time'] > 0)
		   {
		   	  $status = GBS_SUCCEED;
		   }
		   else
		   {
		     $status = GBS_UNDER_WAY;
		   }
        }
    }
    elseif ($group_buy['is_finished'] == GBS_FAIL)
    {
        /* 已处理，团购失败 */
        $status = GBS_FAIL;
    }
	elseif ($group_buy['is_finished'] == GBS_FINISHED)
	{
	   $status = GBS_FINISHED;
	}
	else
	{
	   $status = 5;
	}


    return $status;
}


function get_friend_comment($group_id)
{
	$fcomment = array();
	if($group_id > 0)
	{
	  $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('friend_comment') .
           " WHERE group_id = '$group_id'";
	  $fcomment = $GLOBALS['db']->getAll($sql);  	   
	}
	
	return $fcomment;
}


function insert_now_time($arr = array())
{
     $now = gmtime();
	 if ($arr['act_type'] == 1)
	 {
	  	 $now = $arr['end_date'] > 0 ? $arr['end_date'] : '0';
	 }
	 elseif ($arr['act_type'] == 2)
	 {
	     if ($arr['start_date'] > $now)
		 {
			$now = $arr['start_date'];
		 }
		 else
		 {
		    $now = $arr['end_date'];
		 }
	 }
	$now= local_date("Y,n-1,j,H,i,s",$now);
	return $now;
}

function hdfk_payment_id()
{
    $sql = "SELECT pay_id FROM " . $GLOBALS['ecs']->table('payment') . " WHERE is_cod = 1";

    return $GLOBALS['db']->getCol($sql);
}

function get_group_orders($group_buy_id,$group_need = 1, $is_hdfk = false)
{
   $sql = '';	
   if ($group_need == 1)
   {
      $sql = "SELECT SUM(g.goods_number) AS total_goods " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o, " .
             $GLOBALS['ecs']->table('order_goods') . " AS g " .
            " WHERE o.order_id = g.order_id " .
            "AND o.extension_code = 'group_buy' " .
            "AND o.extension_id = '$group_buy_id' " .
            "AND g.goods_id = '$group_buy_id' ";
   }
   elseif($group_need == 2)
   {
     $sql = "SELECT COUNT(*) AS total_order " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
            " WHERE o.extension_code = 'group_buy'" .
            " AND o.extension_id = '$group_buy_id'";
   }
   elseif($group_need == 3)
   {
     $sql = "SELECT COUNT(DISTINCT o.user_id) AS total_order " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
            " WHERE o.extension_code = 'group_buy'" .
            " AND o.extension_id = '$group_buy_id'";
   }
   if ($is_hdfk)
   {	
	   $sql .= " AND o.order_status " .
               db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
              " AND ( o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
			  " OR o.pay_id " . db_create_in(hdfk_payment_id()) . ") ";	
   }
   else
   {
	    $sql .=  " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART)) .
                 " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
   }
   if ($sql != '')
   {
    $orders_num = $GLOBALS['db']->getOne($sql);
   }
   $orders_num = $orders_num > 0 ? $orders_num : 0;
   return $orders_num;  
}


function get_group_goods()
{

    $sql = "SELECT *, goods_price*goods_number AS all_goods_price,market_price*goods_number AS all_market_price" .
            " FROM " . $GLOBALS['ecs']->table('cart') . " " .
            " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . CART_GROUP_BUY_GOODS . "'";
    $row = $GLOBALS['db']->getRow($sql);
	if (!empty($row))
	{
      if ($row['is_real'] == '1')
      {
		$row['is_fee_shipping'] = true;
	  }
	  else
	  {
	  $row['is_fee_shipping'] = false;
	  }
  
      $row['subtotal']       = $row['all_goods_price'];
	  $row['formated_subtotal']       = group_price_format($row['all_goods_price']);
      $row['formated_goods_price']  = group_price_format($row['goods_price']);
      $row['market_price'] = group_price_format($row['market_price']);
      $row['goods_amount'] = $row['all_goods_price'];
	  
      $ror['saving']       = group_price_format($row['all_market_price'] - $row['all_goods_price']);
      $row['formated_goods_amount']  = group_price_format($row['all_goods_price']);
      $row['formated_market_amount'] = group_price_format($row['all_market_price']);
    }
	else
	{
      $row = array();
	}
    return $row;
}

function get_group_consignee($user_id)
{
    if (isset($_SESSION['flow_consignee']))
    {
        /* 如果存在session，则直接返回session中的收货人信息 */

        return $_SESSION['flow_consignee'];
    }
    else
    {
        /* 如果不存在，则取得用户的默认收货人信息 */
        $arr = array();

        if ($user_id > 0)
        {
            /* 取默认地址 */
            $sql = "SELECT ua.* ".
                    " FROM " . $GLOBALS['ecs']->table('user_address') . "AS ua, ".$GLOBALS['ecs']->table('users').' AS u '.
                    " WHERE u.user_id='$user_id' AND ua.address_id = u.address_id limit 1";
            $arr = $GLOBALS['db']->getRow($sql);
        }
        return $arr;
    }
}

function insert_groupsaled($arr)
{
    $group_buy_id = intval($arr['group_id']);
   
    /* 取得总订单数和总商品数 */
	if ($group_buy_id <= 0)
	{
	  return '';
	  exit;
	}
	$sql = "SELECT group_id,already_orders,group_need,is_hdfk" .
            " FROM " . $GLOBALS['ecs']->table('group_activity') . " WHERE group_id = '$group_buy_id' " ;
    $group_buy = $GLOBALS['db']->getRow($sql);
	$orders_num = get_group_orders($group_buy_id,$group_buy['group_need'],$group_buy['is_hdfk']);
	$orders_num = $orders_num + $group_buy['already_orders'];
	return $orders_num;
}
function insert_group_stats($arr)
{
    $need_cache = $GLOBALS['smarty']->caching;
    $need_compile = $GLOBALS['smarty']->force_compile;

    $GLOBALS['smarty']->caching = false;
    $GLOBALS['smarty']->force_compile = true;

    $group_buy_id = intval($arr['group_id']);
   
    /* 取得总订单数和总商品数 */
	$sql = "SELECT group_id,is_finished ,start_time,end_time,upper_orders,lower_orders,ext_info,already_orders,closed_time" .
	         ",group_restricted,group_need,market_price,succeed_time,group_stock,goods_type,is_hdfk,activity_type" .
            " FROM " . $GLOBALS['ecs']->table('group_activity') . " WHERE group_id = '$group_buy_id' " ;
    $group_buy = $GLOBALS['db']->getRow($sql);
	$orders_num = get_group_orders($group_buy_id,$group_buy['group_need'],$group_buy['is_hdfk']);
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
            $price_ladder[$key]['formated_price'] = $amount_price['price'];
        }
    }
    $group_buy['price_ladder'] = $price_ladder;
	$group_buy['group_price'] = $price_ladder[0]['formated_price'];
	$group_buy['group_rebate'] = number_format($price_ladder[0]['price']/$group_buy['market_price'], 2, '.', '')*10;
	$group_buy['formated_lack_price'] = group_price_format($group_buy['market_price']- $group_buy['group_price']);
	$group_buy['formated_market_price'] = group_price_format($group_buy['market_price']);
	$group_buy['formated_group_price'] = group_price_format($group_buy['group_price']);
    if ($group_buy['is_finished'] == 0)
    {
        /* 未处理 */
		$now = gmtime();
        if ($now < $group_buy['start_time'] && $group_buy['activity_type'] != 3)
        {
            $status = GBS_PRE_START;
        }
        elseif ($now > $group_buy['end_time'] && $group_buy['activity_type'] != 3)
        {
		  $closed_time = $group_buy['end_time'] - 1;
		  $status = $group_buy['succeed_time'] > 0 ? GBS_FINISHED : GBS_FAIL;
		  $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			      " SET is_finished='$status',closed_time='$closed_time' WHERE group_id='$group_buy_id'";
	       $GLOBALS['db']->query($sql);
		  $group_buy['closed_time'] = $closed_time; 
        }
        else
        {
		   	$status = GBS_UNDER_WAY;
		    if (empty($group_buy['succeed_time']) || $group_buy['succeed_time'] == '0')
		    {     
		      if (($orders_num + $group_buy['already_orders']) >= $group_buy['lower_orders'])
		      {
			    if ($orders_num >= $group_buy['lower_orders'])
			    { 
			       $add_time = get_success_time($group_buy_id);
				   $group_buy['succeed_time'] = $add_time;
			     }
			     else
			     { 
			       $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . " SET succeed_time='$now' WHERE group_id='$group_buy_id'";
				   $GLOBALS['db']->query($sql);
				   $group_buy['succeed_time'] = $now;
				   $add_time = $now;
			     }
				 $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . " SET succeed_time='$add_time' WHERE group_id='$group_buy_id'";
				 $GLOBALS['db']->query($sql);

				 if ($orders_num > 0 && (($group_buy['goods_type'] == 1 && !$GLOBALS['_CFG']['make_group_card']) || $group_buy['goods_type'] == 3))
				 {
				   $is_send = !$GLOBALS['_CFG']['send_group_sms'];
				   send_oldgroup_cards($group_buy_id,$is_send);
				 }
			   }
             }
			 if ($group_buy['upper_orders'] > 0)
			 {
				if ($group_buy['upper_orders'] >= $orders_num)
				{ 
			     $group_buy['odd_orders'] = $group_buy['upper_orders'] - $orders_num;
			   }
			   else
			   {
				 $closed_time = $group_buy['end_time'] - 1;
		         $status = $group_buy['succeed_time'] > 0 ? GBS_FINISHED : GBS_FAIL;
		         $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			      " SET is_finished='$status',closed_time='$closed_time' WHERE group_id='$group_buy_id'";
	            $GLOBALS['db']->query($sql);
		        $group_buy['closed_time'] = $closed_time; 
				$group_buy['odd_orders'] = 0;
			   }

			 }
			 else
			 {
			   $group_buy['odd_orders'] = 0;
			 }
	     }
		  
    }
    elseif ($group_buy['is_finished'] == GBS_FAIL)
    {
        /* 已处理，团购失败 */
        $status = GBS_FAIL;
    }
	elseif ($group_buy['is_finished'] == GBS_FINISHED)
	{
	   $status = GBS_FINISHED;
	}
	else
	{
	   $status = 5;
	}
	$group_buy['succeed_time_date'] = local_date('H:i:s', $group_buy['succeed_time']);
	$group_buy['closed_time_date'] = local_date('H:i:s', $group_buy['closed_time']);
	$group_buy['status'] = $status;
	$group_buy['orders_num'] = $orders_num + $group_buy['already_orders'];
	$GLOBALS['smarty']->assign('group_buy', $group_buy);
	if ($group_buy['succeed_time'] > 0)
    {
	  $GLOBALS['smarty']->assign('is_succes', 1);
    }
	if ($group_buy['activity_type'] == 1)
	{
	    $filename = 'library/group_status.lbi';
	}
	elseif ($group_buy['activity_type'] == 2)
	{
	    $filename = 'library/seconds_status.lbi';
	}
	else
	{
	  	$filename = 'library/goods_status.lbi';
	}
	$group_text = $GLOBALS['smarty']->fetch($filename);
    $GLOBALS['smarty']->caching = $need_cache;
    $GLOBALS['smarty']->force_compile = $need_compile;

    return $group_text;

}


function get_group_insert($group_buy_id,$number)
{
   	 $sql = "SELECT group_id,is_finished ,ext_info,is_hdfk,group_need,is_limit" .
	         ",goods_rebate,goods_type,group_restricted,group_stock,market_price,group_name,group_need,upper_orders" .
            " FROM " . $GLOBALS['ecs']->table('group_activity')
		    ." WHERE group_id = '$group_buy_id' " ;

    $group_buy = $GLOBALS['db']->getRow($sql);
	$ext_info = unserialize($group_buy['ext_info']);
    $group_buy = array_merge($group_buy, $ext_info);
	$group_buy['number'] = $number;
	$group_buy['limitnum'] = 0;
    if($group_buy['group_restricted'] > 0 && $group_buy['number'] > $group_buy['group_restricted'])
	{
	   $group_buy['number'] = $group_buy['group_restricted'];
	   $group_buy['limitnum'] = $group_buy['group_restricted'];
	}
    if($group_buy['group_need'] == 1 && $group_buy['upper_orders'] > 0)
	{
		  $group_orders = get_group_orders($group_buy_id,1, $group_buy['is_hdfk']);
		  $surplus_orders = $group_buy['upper_orders'] - $group_orders;
		  $surplus_orders = $surplus_orders >= 0 ? $surplus_orders : 0;
		  if ($group_buy['number'] > $surplus_orders)
		  {
		    $group_buy['number'] = $surplus_orders;
		    $group_buy['limitnum'] = $surplus_orders;  
		  }
     }
     if($group_buy['group_need'] != 1 && $group_buy['group_stock'] > 0)
	 {
		 $group_stock = get_group_orders($group_buy_id,1, $group_buy['is_hdfk']);
		 $surplus_stock = $group_buy['group_stock'] - $group_stock;
		 $surplus_stock = $surplus_stock >= 0 ? $surplus_stock : 0;
		 if ($group_buy['number'] > $surplus_stock)
		 {
		    $group_buy['number'] = $surplus_stock;
		    $group_buy['limitnum'] = $surplus_stock;  
		 }
	 }
	 if($group_buy['goods_type'] == 3)
	 {
	    	$sql = 'SELECT count(*) FROM '. $GLOBALS['ecs']->table('group_card') .
                  " WHERE group_id = '$group_buy_id' AND user_id='0'";  
            $card_num = $GLOBALS['db']->getOne($sql);
			if ($group_buy['number'] > $card_num)
		    {
		      $group_buy['number'] = $card_num;
		      $group_buy['limitnum'] = $card_num;  
		    }
	 }

    /* 处理价格阶梯 */
	$group_price_arr = array();
    $price_ladder = $group_buy['price_ladder'];
    if (!is_array($price_ladder) || empty($price_ladder))
    {
        $price_ladder = array(array('amount' => 0, 'price' => 0));
		$group_buy['group_price'] = '0';
    }
    else
    {
        foreach ($price_ladder as $key => $amount_price)
        {   
		    if ($group_buy['number'] >= $amount_price['amount'])
			{
			    $group_price_arr['amount'] = $amount_price['amount'];
				$group_price_arr['price'] = $amount_price['price'];
			}
        }
    }
	if ($group_price_arr['amount'] == $group_buy['number'])
	{
	  $group_buy['group_price'] = $group_price_arr['price']/$group_price_arr['amount'];
	}
	else
	{
	   $group_buy['group_price'] = $group_price_arr['price']/$group_price_arr['amount'];
	}
    return $group_buy;
}


function get_success_time($group_buy_id)
{
    $sql = "SELECT o.add_time " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
            " WHERE o.extension_code = 'group_buy'" .
            " AND o.extension_id = '$group_buy_id'" .
			" AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
            " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING)) .
			" ORDER BY o.order_id DESC LIMIT 0,1";

	$add_time = $GLOBALS['db']->getOne($sql);
	
    return $add_time;
}

function get_success_num($group_buy_id,$user_id)
{
    $sql = "SELECT count(*) FROM " . $GLOBALS['ecs']->table('order_info') . 
            " WHERE extension_code = 'group_buy'" .
            " AND extension_id = '$group_buy_id'" .
			" AND user_id = '$user_id'" .
			" AND order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED));

	$ordernum = $GLOBALS['db']->getOne($sql);
	
    return $ordernum;
}

function get_shipping_free($group_id, $goods_arr = array('goods_num' => 1), $region = array())
{
     $shipping_fee = 0;
	 $shipping_id = 0;
	 $free_money = 0;
     if ($group_id > 0)
	 {
	   $sql = "SELECT group_freight,goods_weight,shipping_id,pos_express,goods_type FROM " . 
	          $GLOBALS['ecs']->table('group_activity') . " WHERE group_id='$group_id'";
       $group_row = $GLOBALS['db']->getRow($sql);
	   if ($group_row['goods_type'] == 2)
	   {
	     $shipping_id = $group_row['shipping_id'] > 0 ? $group_row['shipping_id'] : '0';
	     if ($shipping_id > 0)
	     {	
           $shipping_info = shipping_area_info($shipping_id, $region);
           if (!empty($shipping_info))
           {
		     $goods_weight = $group_row['goods_weight'] > 0 ? $group_row['goods_weight'] : '0';
		     $goods_arr['goods_num']  = $goods_arr['goods_num'] > 0 ? intval($goods_arr['goods_num']) : '1';
		     $goods_weight = $goods_weight * $goods_arr['goods_num'];
             $free_arr = group_shipping_fee($shipping_info['shipping_code'],$shipping_info['configure'], $goods_weight,$goods_arr['goods_amount'],$goods_arr['goods_num']);
		     $shipping_fee = $free_arr['shipping_fee'];
		     $free_money = $free_arr['free_money'] > 0 ? $free_arr['free_money'] : 0;
	       }
         }
	     else
	     {
		   $shipping_fee = $group_row['group_freight'] > 0 ? $group_row['group_freight'] : '0';	 
	   	   if ($group_row['pos_express'] > 0 && $goods_arr['goods_amount'] >= $group_row['pos_express'])
		   {
		 	  $shipping_fee = 0;
		   }
		   $free_money = $group_row['pos_express'] > 0 ? $group_row['pos_express'] : 0;
	     }
	   }
	 }
	 return array('shipping_fee' => $shipping_fee, 'shipping_id' => $shipping_id, 'free_money' => $free_money);
}
/**
 * 计算运费
 * @param   string  $shipping_code      配送方式代码
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   运费
 */
function group_shipping_fee($shipping_code, $shipping_config, $goods_weight, $goods_amount, $goods_number='')
{
    if (!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }
    $free_arr = array('shipping_free' => 0 , 'free_money' => 0);
    $filename = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
    if (file_exists($filename))
    {
        include_once($filename);

        $obj = new $shipping_code($shipping_config);
	    $free_arr['free_money'] = $obj->configure['free_money'];
        $free_arr['shipping_fee'] = $obj->calculate($goods_weight, $goods_amount, $goods_number);
    }
    return $free_arr;	
}

function insert_group_member_info()
{
    $need_cache = $GLOBALS['smarty']->caching;
    $GLOBALS['smarty']->caching = false;

    if ($_SESSION['user_id'] > 0)
    {
        $GLOBALS['smarty']->assign('user_info', get_user_info());
    }
	$GLOBALS['smarty']->assign('group_shopname',  $GLOBALS['_CFG']['group_shopname']);
    $output = $GLOBALS['smarty']->fetch('library/group_member_info.lbi');

    $GLOBALS['smarty']->caching = $need_cache;

    return $output;
}

function get_group_comment($id = 0,$num = 3)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('comment') .
            " WHERE  comment_type = '2' AND status = 1 AND parent_id = 0";
	if ($id > 0)
	{
	   $sql .= " AND id_value = '$id'";
	}
	$sql .=  " ORDER BY comment_id DESC LIMIT $num";		
    $res = $GLOBALS['db']->query($sql);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['comment_id']]['id']       = $row['comment_id'];
        $arr[$row['comment_id']]['email']    = $row['email'];
        $arr[$row['comment_id']]['username'] = $row['user_name'];
        $arr[$row['comment_id']]['content']  = str_replace('\r\n', '<br />', htmlspecialchars($row['content']));
        $arr[$row['comment_id']]['content']  = nl2br(str_replace('\n', '<br />', $arr[$row['comment_id']]['content']));
        $arr[$row['comment_id']]['rank']     = $row['comment_rank'];
        $arr[$row['comment_id']]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
		$arr[$row['comment_id']]['url']      = rewrite_groupurl('ask.php',array('gid' => $id));
    }
   return $arr;
}

function get_group_comment_count($group_id = '')
{
      $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('comment').
             " WHERE  comment_type = '2' AND status = 1 AND parent_id = 0";
	  if ($group_id > 0)
	  {
	  	$sql .= " AND id_value='$group_id'";
	  }		 
	  $count = $GLOBALS['db']->getOne($sql);
    
     return $count;     
}
function get_group_city()
{
    $sql = 'SELECT city_id,city_name FROM ' . $GLOBALS['ecs']->table('group_city') . " WHERE is_open='1' ORDER BY city_sort DESC,city_id ASC";
    $res = $GLOBALS['db']->query($sql);
	$city_list = array();
    while ($row = $GLOBALS['db']->FetchRow($res))
	{
	   $indexurl = array('team.php','index.php');
	   $row['url'] = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']],array('cityid' => $row['city_id']), true);
	   $city_list[] = $row;
	}
    return $city_list;
}
function get_city_info($city_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('group_city') . " WHERE city_id='$city_id'";
    return $GLOBALS['db']->getRow($sql);
}
function send_group_cards($order_id,$order_sn,$user_id,$mobile, $is_send = true)
{
    $sql = 'SELECT g.goods_id, ga.goods_name,g.is_real,goods_number AS num, ga.succeed_time,ga.past_time FROM '.
           $GLOBALS['ecs']->table('order_goods') . " AS g ," . $GLOBALS['ecs']->table('group_activity') . " AS ga ".
           " WHERE ga.group_id=g.goods_id AND g.order_id = '$order_id' AND g.extension_code = 'group_buy' AND g.is_real in(1,3)";
    $group_buy = $GLOBALS['db']->getRow($sql);
    if (!empty($group_buy))
	{   
	    if ($is_send)
		{
		   include_once(ROOT_PATH.'includes/cls_sms.php');
           $sms = new sms();
	       $tpl = get_sms_template('send_sms');
		}
	    $add_date = gmtime();
		$group_id = $group_buy['goods_id'];
		$sql = 'SELECT * FROM '. $GLOBALS['ecs']->table('group_card') .
                " WHERE group_id = '$group_id' AND user_id = '$user_id' AND order_sn='$order_sn'";  
        $res = $GLOBALS['db']->getAll($sql);
        if ($res)
	    { 
		   if ($is_send)
		   {
             foreach ($res as $row)
            {  
			  
			   if ($GLOBALS['_CFG']['send_sms_num'] > 0 && $row['send_num'] >= $GLOBALS['_CFG']['send_sms_num'])
			   {
			      continue;
			   }
			   $GLOBALS['smarty']->assign('group_name', $group_buy['goods_name']);
		       $GLOBALS['smarty']->assign('card_sn', $row['card_sn']);
		       $GLOBALS['smarty']->assign('card_password', $row['card_password']);
		       $GLOBALS['smarty']->assign('past_time',  local_date('Y-m-d', $row['end_date']));
		       $msg = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
			   if ($sms->send($mobile, $msg, 0))
		       {
		         $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET send_num=send_num+1 WHERE card_id='$row[card_id]'";
	             $GLOBALS['db']->query($sql);
		       } 
            }
		   }
 	    }
		else
	    {
		   $is_saled  = 0;
		  if ($group_buy['succeed_time'] > 0)
		  {
			 $is_saled = 1;
		  }	
		  $end_date = $group_buy['past_time'];
		  $new_cards = array();
		  if ($group_buy['is_real'] == 1)
		  {
		    srand((double)microtime()*1000000); 
            $randval = rand();
		    for ($i = 0; $i < $group_buy['num']; $i++)
		    {
                
		      $card_sn = str_pad(mt_rand(0, 99999999), 6, '0', STR_PAD_LEFT);
			  $card_password = get_rndcode();
			  $sql = "INSERT INTO ".$GLOBALS['ecs']->table('group_card').
				       "(group_id,card_sn,card_password,add_date,order_sn,user_id,end_date,is_saled)". 
				       "VALUES('$group_id', '$card_sn','$card_password','$add_date','$order_sn','$user_id','$end_date','$is_saled')";
              $GLOBALS['db']->query($sql);
			  $card_id = $GLOBALS['db']->insert_id(); 
			  $cards = array('card_id'=>$card_id,'card_sn' => $card_sn, 'card_password' => $card_password);
			  $new_cards[] = $cards;
		    }
		  }
		  elseif($group_buy['is_real'] == 3)
		  {
			for ($i = 0; $i < $group_buy['num']; $i++)
		    {
               $group_id = $group_buy['goods_id'];
		       $sql = 'SELECT card_id,card_sn,card_password FROM '. $GLOBALS['ecs']->table('group_card') .
                  " WHERE group_id = '$group_id' AND user_id = '0' ORDER BY card_id DESC LIMIT 1";
               $card_arr = $GLOBALS['db']->getRow($sql);
			   if ($card_arr['card_id'] > 0)
			   {
				 $card_id = $card_arr['card_id'];
				 $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . 
				        " SET order_sn='$order_sn',user_id='$user_id',end_date='$end_date',is_saled='$is_saled'" .
						" WHERE card_id='$card_id'";
	              $GLOBALS['db']->query($sql);
				 $cards = array(
				                'card_id'=>$card_id, 
								'card_sn' => $card_arr['card_sn'], 
								'card_password' => $card_arr['card_password']
							    ); 
			     $new_cards[] = $cards;
			   }
			 }
			 $sql = 'SELECT count(*) FROM '. $GLOBALS['ecs']->table('group_card') .
                  " WHERE group_id = '$group_id' AND user_id='0'";  
             $card_num = $GLOBALS['db']->getOne($sql);
 		     if (!$card_num)
			 {
				 $status = GBS_FINISHED;
				 $now = gmtime();
			     $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			            " SET is_finished='$status',closed_time='$now' WHERE group_id='$group_id'";
			     $GLOBALS['db']->query($sql);
			 }
		  }
		   if ($is_send && $group_buy['succeed_time'] > 0)
			{ 
			    foreach ($new_cards as $cards)
		       {
				 $GLOBALS['smarty']->assign('group_name', $group_buy['goods_name']);
		         $GLOBALS['smarty']->assign('card_sn', $cards['card_sn']);
		         $GLOBALS['smarty']->assign('card_password', $cards['card_password']);
		         $GLOBALS['smarty']->assign('past_time',  local_date('Y-m-d', $end_date));
		         $msg = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
                 if ($sms->send($mobile, $msg, 0))
		         {
					$card_id = $cards['card_id']; 
		            $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET send_num=1 WHERE card_id='$card_id'";
	                $GLOBALS['db']->query($sql);
		         } 

                }
		     }
		  }
		}
	 $sql = 'SELECT count(*) FROM '. $GLOBALS['ecs']->table('group_card') ." WHERE order_sn='$order_sn'";  
	 $gsql = 'SELECT SUM(goods_number) FROM '. $GLOBALS['ecs']->table('order_goods') ." WHERE order_id='$order_id'";  
     $card_num = $GLOBALS['db']->getOne($sql);
	 $goods_num = $GLOBALS['db']->getOne($gsql);
	 if ($card_num == $goods_num)
	 {
		$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') .
                         " SET shipping_status='".SS_SHIPPED.
					     "',shipping_time = '".gmtime()."'" .
                         " WHERE order_id = '$order_id'";
        $GLOBALS['db']->query($sql);
	 }		
    return true;
}

function set_group_stats($group_buy_id)
{
	$sql = "SELECT group_id,is_finished ,start_time,end_time,upper_orders,lower_orders" .
	         ",group_restricted,group_stock,succeed_time,goods_type,group_need,is_hdfk,already_orders FROM " .
              $GLOBALS['ecs']->table('group_activity') . 
			 " WHERE group_id = '$group_buy_id' AND is_finished = 0";
    $group_buy = $GLOBALS['db']->getRow($sql);
    $orders_num = get_group_orders($group_buy_id,$group_buy['group_need'],$group_buy['is_hdfk']);
    if (!empty($group_buy))
	{
	   $now = gmtime();
       if ($now < $group_buy['start_time'] && $group_buy['activity_type'] != 3)
      {
           $status = GBS_PRE_START;
      }
      elseif ($now >= $group_buy['end_time'] && $group_buy['activity_type'] != 3)
      {
		  $closed_time = $group_buy['end_time'] - 1;
		  $status = $group_buy['succeed_time'] > 0 ? GBS_FINISHED : GBS_FAIL;
		  $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			      " SET is_finished='$status',closed_time='$closed_time' WHERE group_id='$group_buy_id'";
	       $GLOBALS['db']->query($sql);
      }
      else
      {
	     $status = GBS_UNDER_WAY;
		 if (empty($group_buy['succeed_time']) || $group_buy['succeed_time'] == '0')
		 {     
		     if (($orders_num + $group_buy['already_orders']) >= $group_buy['lower_orders'])
		     {
			     if ($orders_num >= $group_buy['lower_orders'])
			     { 
			       $add_time = get_success_time($group_buy_id);
			     }
			     else
			     { 
				   $add_time = $now;
			     }
				 $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') .
				           " SET succeed_time='$add_time' WHERE group_id='$group_buy_id'";
				 $GLOBALS['db']->query($sql);
				 $group_buy['succeed_time'] = $add_time;

				 if ($orders_num > 0 && (($group_buy['goods_type'] == 1 && !$GLOBALS['_CFG']['make_group_card']) || $group_buy['goods_type'] == 3))
				 {
				   $is_send = !$GLOBALS['_CFG']['send_group_sms'];
				   send_oldgroup_cards($group_buy_id,$is_send);
				 }
			  }
		 }
		 if ($group_buy['upper_orders'] > 0 && $orders_num >= $group_buy['upper_orders'])
		 {
				$status = 5;
			    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			           " SET is_finished='$status',closed_time='$now' WHERE group_id='$group_buy_id'";
			    $GLOBALS['db']->query($sql);
		  }
		  if($group_buy['group_need'] !=1 && $group_buy['group_stock'] > 0)
		  {
				$group_stock = get_group_orders($group_buy_id,1, $group_buy['is_hdfk']);
				if ($group_stock >= $group_buy['group_stock'])
				{
					  $status = 5;
			          $sql = 'UPDATE ' . $GLOBALS['ecs']->table('group_activity') . 
			             " SET is_finished='$status',closed_time='$now' WHERE group_id='$group_buy_id'";
			          $GLOBALS['db']->query($sql);
                }
		    }
		}	
     }
     return $status;	
}

function send_oldgroup_cards($group_buy_id, $is_send = true)
{
  if($is_send)
  {
    $sql = 'SELECT * FROM '. $GLOBALS['ecs']->table('group_card') .
           " WHERE group_id = '$group_buy_id' AND is_saled = 0 AND user_id > 0";
    $res = $GLOBALS['db']->query($sql);
	include_once(ROOT_PATH.'includes/cls_sms.php');
    $sms = new sms();
    if ($res)
	{ 
	  $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET is_saled='1' WHERE group_id='$group_buy_id'";
	  $GLOBALS['db']->query($sql); 
	  $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('group_activity') . " WHERE group_id ='$group_buy_id'";
      $group_name = $GLOBALS['db']->getOne($sql);
	  $tpl = get_sms_template('send_sms');
      while ($row = $GLOBALS['db']->fetchRow($res))
      {  
		  $GLOBALS['smarty']->assign('group_name', $group_name);
		  $GLOBALS['smarty']->assign('card_sn', $row['card_sn']);
		  $GLOBALS['smarty']->assign('card_password', $row['card_password']);
		  $GLOBALS['smarty']->assign('past_time',  local_date('Y-m-d', $row['end_date']));
		  $msg = $GLOBALS['smarty']->fetch('str:' . $tpl['template_content']);
		  $sql = 'SELECT mobile FROM ' . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '$row[order_sn]'";
		  $mobile = $GLOBALS['db']->getOne($sql);
          if ($sms->send($mobile, $msg, 0))
		  {
		    $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') . " SET send_num=1 WHERE card_id='$row[card_id]'";
	        $GLOBALS['db']->query($sql);
		  } 
	   }
	 }
   }
   else
   {
      $sql = "UPDATE " . $GLOBALS['ecs']->table('group_card') .
	          " SET is_saled='1' WHERE group_id='$group_buy_id' AND user_id > 0";
	  $GLOBALS['db']->query($sql); 
   }
    return true;
}

function get_rndcode()
{
	$str='ABCDEFGHIJKLMNOPGRSTUVWXYZ';
	$rndstr = '';
    for($i = 0; $i < 6; $i++)  
   {  
     $rndcode=rand(0,25);  
     $rndstr.=$str[$rndcode];  
   }  
   return $rndstr;  
}
function set_affiliate_log($order_id, $uid, $money, $point = 0, $separate_by = 1)
{
    $time = gmtime();
	$username = $GLOBALS['db']->getOne("SELECT user_name FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id='$uid'" );
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('affiliate_log') . 
	        "( order_id, user_id, user_name, time, money, point, separate_type)".
             " VALUES ( '$order_id', '$uid', '$username', '$time', '$money', '$point', $separate_by)";
    if ($order_id > 0)
    {
        $GLOBALS['db']->query($sql);
    }
}

function get_sms_template($tpl_name)
{
    $sql = 'SELECT template_subject, is_html, template_content FROM ' .
	        $GLOBALS['ecs']->table('mail_templates') . " WHERE template_code = '$tpl_name'";

    return $GLOBALS['db']->getRow($sql);

}

function assign_public($city_id)
{
	$city_info = get_city_info($city_id);
	$shop_notice = $GLOBALS['_CFG']['group_notice'] != '' ? $GLOBALS['_CFG']['group_notice'] : $city_info['city_notice'];
	$shop_qq = $city_info['city_qq'] != '' ? $city_info['city_qq'] : $GLOBALS['_CFG']['group_qq'];

	$links = get_weblinks();
    $GLOBALS['smarty']->assign('img_links',       $links['img']);
    $GLOBALS['smarty']->assign('txt_links',       $links['txt']);
    $GLOBALS['smarty']->assign('data_dir',        DATA_DIR);       // 数据目录 
    $GLOBALS['smarty']->assign('cityid', $city_id);
    $GLOBALS['smarty']->assign('group_city', get_group_city());
	$GLOBALS['smarty']->assign('city_info',  $city_info);
	$GLOBALS['smarty']->assign('weburl',     $GLOBALS['ecs']->get_domain() . '/');
	$GLOBALS['smarty']->assign('ecgweburl',  $GLOBALS['ecs']->url());
	$GLOBALS['smarty']->assign('group_help', get_group_help());
	$GLOBALS['smarty']->assign('navigation', set_navigation($city_id));
	$GLOBALS['smarty']->assign('group_cardname',  $GLOBALS['_CFG']['group_cardname']);
    $GLOBALS['smarty']->assign('group_notice',  $shop_notice);
	$GLOBALS['smarty']->assign('group_qq',     $shop_qq);
	$indexurl = array('team.php','index.php');
	$url = rewrite_groupurl($indexurl[$GLOBALS['_CFG']['groupindex']]);
	$GLOBALS['smarty']->assign('index_url',        $url);
	$GLOBALS['smarty']->assign('group_logo',      $GLOBALS['_CFG']['group_logo']);
	$GLOBALS['smarty']->assign('group_phone',     $GLOBALS['_CFG']['group_phone']); 
	$GLOBALS['smarty']->assign('group_shopname',  $GLOBALS['_CFG']['group_shopname']);
	$GLOBALS['smarty']->assign('group_statscode', $GLOBALS['_CFG']['group_statscode']);
	$GLOBALS['smarty']->assign('group_shoptitle', $GLOBALS['_CFG']['group_shoptitle']); 
	$GLOBALS['smarty']->assign('group_shopdesc',  $GLOBALS['_CFG']['group_shopdesc']);
	$GLOBALS['smarty']->assign('group_shopaddress',  $GLOBALS['_CFG']['group_shopaddress']);
}

function get_group_help()
{
    $sql = 'SELECT c.cat_id, c.cat_name, c.sort_order, a.article_id, a.title, a.file_url,a.link, a.open_type ' .
            'FROM ' .$GLOBALS['ecs']->table('article'). ' AS a,' .
            $GLOBALS['ecs']->table('article_cat'). ' AS c ' .
            'WHERE a.cat_id = c.cat_id AND a.is_open = 1 AND c.parent_id=100 ' .
            'ORDER BY c.sort_order ASC, a.article_id';
    $res = $GLOBALS['db']->query($sql);
    $arr = array();
	$idx = 0;
	while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['cat_id']]['cat_name']                     = $row['cat_name'];
        $arr[$row['cat_id']]['article'][$idx]['article_id']  = $row['article_id'];
        $arr[$row['cat_id']]['article'][$idx]['title']       = $row['title'];
		if ($row['link'] != 'http://' && $row['link'] != '')
		{
		   $arr[$row['cat_id']]['article'][$idx]['url']  = $row['link'];
		}
		else
		{
		  $arr[$row['cat_id']]['article'][$idx]['url']       = rewrite_groupurl('help.php', array('id'=>$row['article_id']));
		}
		$idx++;
    }
    return $arr;
}

function get_weblinks()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['ecs']->table('friend_link') . ' ORDER BY show_order LIMIT 20';
    $res = $GLOBALS['db']->query($sql);
	$links['img'] = $links['txt'] = array();
 	while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
									'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                   'url'  => $row['link_url']);
		}
    }
    return $links;
}

function group_price_format($price)
{
    return sprintf($GLOBALS['_CFG']['group_format'], $price);
}

function rewrite_groupurl($url, $param = array(), $is_use = false)
{
	$param_url = '';
   if ($GLOBALS['_CFG']['group_rewrite'] == 1)
	{
       $url = substr_replace ($url,'',-4);
	   $url_suffix = '.html'; 
	   if (!empty($param))
	   {
	      $url .= '-';
	      if ($is_use)
		  {
		     foreach ($param AS $key => $value)
             {
               $param_url .= $key . '-' . $value . '-';
             }
			  $param_url = trim($param_url,'-');
		  }
		  else
		  {
	       $param_url = join('-',$param);
		  }
       }
	}
    else
	{  
	   $url_suffix = '';
	    if (!empty($param))
	   {  $param_url = '?';
          foreach ($param AS $key => $value)
         {
           $param_url .= $key . '=' . $value . '&';
         }
		 $param_url = trim($param_url,'&');
	   }
	}
	$url .= $param_url . $url_suffix;
   return $url;
}
function get_group_pager($url, $param, $record_count, $page = 1, $size = 10)
{
    $size = intval($size);
    if ($size < 1)
    {
        $size = 10;
    }
    $page = intval($page);
    if ($page < 1)
    {
        $page = 1;
    }
    $record_count = intval($record_count);
    $page_count = $record_count > 0 ? intval(ceil($record_count / $size)) : 1;
    if ($page > $page_count)
    {
       $page = $page_count;
    }
	
    /* 分页样式 */

    $pager['styleid'] = isset($GLOBALS['_CFG']['page_style'])? intval($GLOBALS['_CFG']['page_style']) : 0;
    $page_prev  = ($page > 1) ? $page - 1 : 1;
    $page_next  = ($page < $page_count) ? $page + 1 : $page_count;
    /* 将参数合成url字串 */
	if ($GLOBALS['_CFG']['group_rewrite'] == 1)
	{
       $url = substr_replace ($url,'',-4);
	   $param_url = ''; 
	    foreach ($param AS $key => $value)
        {
		  if (!empty($value))
		  {
           $param_url .= $key . '-' . $value;
		  }
        }
	}
    else
	{  
	   $param_url = '?';
	    if (!empty($param))
	   {
         foreach ($param AS $key => $value)
        {
           $param_url .= $key . '=' . $value . '&';
        }
	   }
	}
    $pager['url']          = $url;
    $pager['start']        = ($page -1) * $size;
    $pager['page']         = $page;
    $pager['size']         = $size;
    $pager['record_count'] = $record_count;
    $pager['page_count']   = $page_count;
    if ($pager['styleid'] == 0)
    {
	    if ($GLOBALS['_CFG']['group_rewrite'] == 1)
		{
          $pager['page_first']   = $url .'-' . $param_url . '-1.html';
          $pager['page_prev']    = $url .'-' . $param_url .'-' . $page_prev . '.html';
          $pager['page_next']    = $url .'-' . $param_url .'-' . $page_next . '.html';
          $pager['page_last']    = $url .'-' . $param_url .'-' . $page_count . '.html';	    
		}
        else
		{
          $pager['page_first']   = $url . $param_url . 'page=1';
          $pager['page_prev']    = $url . $param_url . 'page=' . $page_prev;
          $pager['page_next']    = $url . $param_url . 'page=' . $page_next;
          $pager['page_last']    = $url . $param_url . 'page=' . $page_count;
		}
        $pager['array']  = array();
        for ($i = 1; $i <= $page_count; $i++)
        {
            $pager['array'][$i] = $i;
        }
    }
    else
    {
        $_pagenum = 10;     // 显示的页码
        $_offset = 2;       // 当前页偏移值
        $_from = $_to = 0;  // 开始页, 结束页
        if($_pagenum > $page_count)
        {
            $_from = 1;
            $_to = $page_count;
        }
        else
        {
            $_from = $page - $_offset;
            $_to = $_from + $_pagenum - 1;
            if($_from < 1)
            {
                $_to = $page + 1 - $_from;
                $_from = 1;
                if($_to - $_from < $_pagenum)
                {
                    $_to = $_pagenum;
                }
            }
            elseif($_to > $page_count)
            {
                $_from = $page_count - $_pagenum + 1;
                $_to = $page_count;
            }
        }
		if ($GLOBALS['_CFG']['group_rewrite'] == 1)
		{ 
	
          $url_format = $param_url != '' ? $url . '-' . $param_url . '-' : $url . '-';
		  $url_suffix = '.html';
	      $pager['page_first'] =  ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 . $url_suffix: '';
          $pager['page_prev']  = ($page > 1) ? $url_format . $page_prev . $url_suffix: '';
          $pager['page_next']  = ($page < $page_count) ? $url_format . $page_next . $url_suffix: '';
          $pager['page_last']  = ($_to < $page_count) ? $url_format . $page_count . $url_suffix: '';
		}
		else
		{
		   $url_format = $url . $param_url . 'page=';
		   $url_suffix = '';
          $pager['page_first'] = ($page - $_offset > 1 && $_pagenum < $page_count) ? $url_format . 1 : '';
          $pager['page_prev']  = ($page > 1) ? $url_format . $page_prev : '';
          $pager['page_next']  = ($page < $page_count) ? $url_format . $page_next : '';
          $pager['page_last']  = ($_to < $page_count) ? $url_format . $page_count : '';
		}
		$pager['page_kbd']  = ($_pagenum < $page_count) ? true : false;
        for ($i=$_from;$i<=$_to;++$i)
        {
            $pager['page_number'][$i] = $url_format . $i . $url_suffix;
        }
    }
    return $pager;

}
function set_navigation($city_id)
{
   $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('group_navigation') . ' WHERE is_show = 1 ORDER BY show_order DESC';
   $res = $GLOBALS['db']->query($sql);
   $url_arr;
   $nav = 0;
   while ($row = $GLOBALS['db']->fetchRow($res))
   {
        if (substr($row['nav_url'],0,7) == 'http://')
		{
		   $keyname = 'other_url_' . $nav;
		   $nav++;
		}
		else
		{
          $start = strrpos($row['nav_url'],'/');
		  $start = !$start ? 0 : $start;
		  $end   = strrpos($row['nav_url'],'.');
		  $keyname = substr($row['nav_url'],$start,$end-$start);
		  if (in_array($keyname,array('stage','hots','seconds','partner')))
		  {
			 $row['nav_url'] = rewrite_groupurl($row['nav_url'],array('cityid'=>$city_id),true); 
		  }
		  else
		  {
		   $row['nav_url'] = rewrite_groupurl($row['nav_url']);
		  }
		}
		$url_arr[$keyname] = array('name' => $row['nav_name'], 'url' => $row['nav_url']);
    }
    return $url_arr;				   
}

function get_group_properties($group_id,$group_attr_id = array())
{
    /* 对属性进行重新排序和分组 */
    $sql = "SELECT attr_group ".
            "FROM " . $GLOBALS['ecs']->table('goods_type') . " AS gt, " . $GLOBALS['ecs']->table('group_activity') . " AS ga ".
            "WHERE ga.group_id='$group_id' AND gt.cat_id=ga.group_attr";
    $grp = $GLOBALS['db']->getOne($sql);
    if (!empty($grp))
    {
        $groups = explode("\n", strtr($grp, "\r", ''));
    }

    /* 获得商品的规格 */
    $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ".
                "g.group_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . $GLOBALS['ecs']->table('group_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            "WHERE g.group_id = '$group_id' " .
            'ORDER BY a.sort_order, g.attr_price, g.group_attr_id';
    $res = $GLOBALS['db']->query($sql);
	$arr = array();
 	while ($row = $GLOBALS['db']->fetchRow($res))    
	{
		$is_selected = '0';
		if (!empty($group_attr_id) && in_array($row['group_attr_id'],$group_attr_id))
		{
		  $is_selected = 1;
		}
        $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

        $arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
        $arr[$row['attr_id']]['name']     = $row['attr_name'];
        $arr[$row['attr_id']]['values'][] = array(
                                                     'label'        => $row['attr_value'],
                                                     'price'        => $row['attr_price'],
                                                     'format_price' => group_price_format(abs($row['attr_price'])),
                                                     'id'           => $row['group_attr_id'],
													 'selected'     => $is_selected
												  );
    }
    return $arr;
}

function get_group_attr_info($arr)
{
    $attr   = '';

    if (!empty($arr))
    {
        $fmt = "%s:%s[%s] \n";

        $sql = "SELECT a.attr_name, ga.attr_value, ga.attr_price ".
                "FROM ".$GLOBALS['ecs']->table('group_attr')." AS ga, ".
                    $GLOBALS['ecs']->table('attribute')." AS a ".
                "WHERE " .db_create_in($arr, 'ga.group_attr_id')." AND a.attr_id = ga.attr_id";
        $res = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchRow($res))
        {
            $attr_price = round(floatval($row['attr_price']), 2);
            $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], group_price_format($attr_price));
        }

        $attr = str_replace('[0]', '', $attr);
    }

    return $attr;
}

function group_spec_price($spec)
{
    if (!empty($spec))
    {
        $where = db_create_in($spec, 'group_attr_id');

        $sql = 'SELECT SUM(attr_price) AS attr_price FROM ' . $GLOBALS['ecs']->table('group_attr') . " WHERE $where";
        $price = floatval($GLOBALS['db']->getOne($sql));
    }
    else
    {
        $price = 0;
    }

    return $price;
}


function show_group_message($content, $links = '', $hrefs = '', $type = 'info', $auto_redirect = true)
{
    $msg['content'] = $content;
    if (is_array($links) && is_array($hrefs))
    {
        if (!empty($links) && count($links) == count($hrefs))
        {
            foreach($links as $key =>$val)
            {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];
        }
    }
    else
    {
        $link   = empty($links) ? $GLOBALS['_LANG']['back_up_page'] : $links;
        $href    = empty($hrefs) ? 'javascript:history.back()'       : $hrefs;
        $msg['url_info'][$link] = $href;
        $msg['back_url'] = $href;
    }

    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS['smarty']->assign('message', $msg);
    $GLOBALS['smarty']->display('group_message.dwt');

    exit;
}

function get_expand_city($city_id)
{
    $expand_city_array = '';
    $sql = 'SELECT group_id FROM ' . $GLOBALS['ecs']->table('expand_city') . " WHERE city_id='$city_id'";
    $expand_city_array = $GLOBALS['db']->getCol($sql);
    return db_create_in($expand_city_array, 'group_id');
}

function group_class_list($class_type = 1, $is_show = false, $filename = 'stage.php')
{
   $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('group_class'). " WHERE class_type='$class_type'";
   if ($is_show)
   {
     $sql .= ' AND is_show = 1';
   }
   $class_list = array();
   $res = $GLOBALS['db']->query($sql);
   while ($row = $GLOBALS['db']->fetchRow($res))
   {
   	  $row['url'] = rewrite_groupurl($filename,array('catid' => $row['cid']),true);
	  $class_list[] = $row;
   }
    return $class_list;

}

function group_payment_list($is_online = false ,$is_hdfk = false)
{
    $sql = 'SELECT pay_id, pay_code, pay_name, pay_fee, pay_desc, pay_config, is_cod' .
            ' FROM ' . $GLOBALS['ecs']->table('payment') .
            " WHERE enabled = 1 AND pay_code <> 'balance'";
    if ($is_online)
    {
	   if ($is_hdfk)
	   {
        $sql .= "AND (is_online = '1' OR pay_code='cod')";
	   }
	   else
	   {
	     $sql .= "AND is_online = '1'";
	   }
    }
	    $sql .= 'ORDER BY pay_order'; // 排序
    $pay_list = $GLOBALS['db']->getAll($sql);
   
    return $pay_list;
}

function set_group_rebate($group_id,$parent_id,$user_id,$order_id,$order_sn)
{
	if (!$GLOBALS['_CFG']['group_rebate'] && $parent_id > 0 && $user_id > 0 && $group_id > 0 && $order_id > 0 && $order_sn > 0)
	{
	    $sql = 'SELECT goods_rebate,goods_type FROM '. $GLOBALS['ecs']->table('group_activity') . 
                   " WHERE group_id='$group_id'";  
	    $group_buy =  $GLOBALS['db']->getRow($sql);
		$sql = "SELECT COUNT(*)  FROM " . $GLOBALS['ecs']->table('order_info') . " AS o " .
                " WHERE  o.extension_code = 'group_buy' " .
				" AND o.parent_id='$parent_id' AND o.user_id='$user_id'" .
                " AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_SPLITED)) .
                " AND o.pay_status " . db_create_in(array(PS_PAYED, PS_PAYING));
	     if ($GLOBALS['db']->getOne($sql) == 1 && $group_buy['goods_rebate'] > 0)
		 {
				 $info = sprintf('邀请返利', $order_sn , $group_buy['goods_rebate'],0);
                 log_account_change($parent_id, $group_buy['goods_rebate'], 0, 0, 0, $info);
				 set_affiliate_log($order_id, $parent_id, $group_buy['goods_rebate']);
				 $sql = "UPDATE " . $GLOBALS['ecs']->table('order_info') .
                         " SET is_separate = 1" . " WHERE order_id = '$order_id'";
                 $GLOBALS['db']->query($sql);
		 }
	 }			  
}
function set_grouporders_stats($group_buy_id)
{
    if ($group_buy_id > 0)
	{	
	  include_once(ROOT_PATH.'includes/lib_order.php');
	
      $sql = "SELECT order_id,order_sn,user_id,bonus_id,surplus,money_paid,integral FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE o.extension_code = 'group_buy'" .
            " AND o.extension_id = '$group_buy_id'" . 
			" AND o.order_status " . db_create_in(array(OS_UNCONFIRMED, OS_CONFIRMED)) .
            " AND o.shipping_status " . db_create_in(array(SS_UNSHIPPED, SS_PREPARING)) .
            " AND o.pay_status = '" . PS_UNPAYED . "' ";
      $res = $GLOBALS['db']->query($sql);
       while ($order = $GLOBALS['db']->fetchRow($res))
      {
        $order_id = $order['order_id'];
        /* 标记订单为“无效” */
        update_order($order_id, array('order_status' => OS_INVALID));
         /* 记录log */
		$action_note = '团购结束,订单无效';
        order_action($order['order_sn'], OS_INVALID, SS_UNSHIPPED, PS_UNPAYED, $action_note);
        /* 退还用户余额、积分、红包 */
        return_user_surplus_integral_bonus($order);
       }
	}
}
    function get_small_suppliers($pid = 0)
	{
		$pArr = array();
		if ($pid > 0)
		{
	  	  $psql = "SELECT suppliers_id, suppliers_name,phone,linkman,website,suppliers_desc,address FROM " . 
	           $GLOBALS['ecs']->table("suppliers") . " WHERE parent_id='$pid' AND is_show = 1";	
	      $pArr = $GLOBALS['db']->getAll($psql);
		}
        return $pArr;
	}	
function insert_last_order()
{
   $order_str = '';
   if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0)
   {
      $last_order = get_last_order($_SESSION['user_id']);
	  if (!empty($last_order))
	  {
	    $order_str = "<div class='orderPost clearfix'><span class='lf'>您的订单" . $last_order['order_sn'] . '还没有付款 !!!</span>'.
	      "<span class='rf'><a href='check.php?id=" . $last_order['order_id'] . "' target='_blank' style='color:#f60;'>点击付款 >></a></span></div>";
	  }		   
   }
   return $order_str;
}
function get_last_order($user_id)
{
    $sql = "SELECT o.order_id,o.order_sn,o.extension_id " .
            "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o" .
            " WHERE o.extension_code = 'group_buy'" .
            " AND o.user_id = '$user_id'" .
			" AND o.order_status " . db_create_in(array(OS_CONFIRMED, OS_UNCONFIRMED)) .
            " AND o.pay_status NOT" . db_create_in(array(PS_PAYED, PS_PAYING)) .
			" ORDER BY o.order_id DESC LIMIT 0,1";

	$last_order = $GLOBALS['db']->getRow($sql);
	$group_buy_id = $last_order['extension_id'];
	$order = array();
    if ($group_buy_id > 0)
	{	
	  $sql = "SELECT is_finished ,start_time,end_time,goods_name FROM " . $GLOBALS['ecs']->table('group_activity') . 
			" WHERE group_id = '$group_buy_id'";
	  $group_arr = $GLOBALS['db']->getRow($sql);
	  $now = gmtime();
	  if ($group_arr['is_finished'] == '0' && $group_arr['end_time'] >= $now)
	  {
	     $order['goods_name'] = $group_arr['goods_name'];
		 $order['order_id'] = $last_order['order_id'];
		 $order['order_sn'] = $last_order['order_sn'];
	  }
	}
    return $order;
}
function get_loginconfig($webfrom = '')
{
   $appconfig = array();	
   if ($webfrom != '')
   {	
    $sql = "SELECT app_secret,app_key,app_encrypt FROM ".$GLOBALS['ecs']->table('login_config') . " WHERE web_from='$webfrom' AND is_open=1";
    $appconfig = $GLOBALS['db']->getRow($sql);
   }
   return $appconfig;
}
function get_loginopen()
{
   $sql = "SELECT web_login,login_img FROM ".$GLOBALS['ecs']->table('login_config') . " WHERE is_open=1 ORDER BY sort_order DESC";
   $appconfig = $GLOBALS['db']->getAll($sql);
   return $appconfig;
}

?>