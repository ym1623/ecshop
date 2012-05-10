<?php

/**
 * ECGROUPON 团购商品前台文件
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
     $smarty->tmp_dir = 'template/' . $_CFG['formwork'] . '/';
	 $smarty->template_dir = ROOT_PATH . 'template/' . $_CFG['formwork'];
    /* 取得参数：团购活动id */
    $group_buy_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';

    $city_id  = (isset($_REQUEST['cityid']) && intval($_REQUEST['cityid']) > 0) ? intval($_REQUEST['cityid'])   : (isset($_COOKIE['ECS']['cityid']) ? $_COOKIE['ECS']['cityid'] :  $_CFG['group_city']);
    $cat_id = 0;
    setcookie('ECS[cityid]', $city_id, gmtime() + 86400 * 7);
    /* 缓存id：语言，团购活动id，状态，（如果是进行中）当前数量和是否登录 */
    $cache_id = $_CFG['lang'] . '-' . $group_buy_id . '-' .  $_SESSION['user_rank'] . '-' . $city_id . '-' . $cat_id . date('z');
    $cache_id = sprintf('%X', crc32($cache_id));
     /* 如果没有缓存，生成缓存 */
     $mode_temp = array('group_team.dwt','group_more.dwt');
	 $mode_id = (isset($_CFG['showmode']) && $_CFG['showmode'] == 1) ? 1 : 0;
	 $mode_id = (isset($group_buy_id) && $group_buy_id >= 1) ? 0 : $mode_id;
	 if (isset($_REQUEST['from']) && $_REQUEST['from']!='')
	 {
	    require_once(ROOT_PATH . 'includes/lib_grouplogin.php');
        ecglogin($_REQUEST['from']);
	 }
    if (!$smarty->is_cached($mode_temp[$mode_id], $cache_id))    {  
		
		assign_public($city_id);
		$vote = get_vote();
        if (!empty($vote))
        {
         $smarty->assign('vote_id',     $vote['id']);
         $smarty->assign('vote',        $vote['content']);
        }
	    if ($mode_id == 0)
		{
          $group_buy = get_group_buy_info($group_buy_id,$city_id,$cat_id);
		  if (empty($group_buy))
		  {
			$url = rewrite_groupurl('subscribe.php');
            ecs_header("Location: $url\n");
            exit; 
		  }
          $group_type = array('1'=>'团购','2'=>'秒杀','3'=>'热销');
          $smarty->assign('group_buy', $group_buy);
		  $smarty->assign('type_name', $group_type[$group_buy['activity_type']]);

          $sql = "SELECT * FROM " . $ecs->table('suppliers') . " WHERE suppliers_id='$group_buy[suppliers_id]'";
		  $suppliers_arr = $db->getRow($sql);
		  $address_img =  'template/meituan/images/temp-ditu.gif';
		  $suppliers_arr['address_img'] = empty($suppliers_arr['address_img']) ? $address_img : $suppliers_arr['address_img'];
		  $smarty->assign('suppliers_arr', $suppliers_arr);
		  $smarty->assign('small_suppliers', get_small_suppliers($group_buy['suppliers_id']));
		  $smarty->assign('today_group',  get_today_grouplist($group_buy['group_id'],$city_id, $cat_id,$group_buy['activity_type']));
          $smarty->assign('friend_comment', get_friend_comment($group_buy['group_id']));
		  $sql = "SELECT * FROM " . $ecs->table('group_gallery') . " WHERE group_id = '$group_buy[group_id]'";
          $img_list = $db->getAll($sql);
          $smarty->assign('img_list', $img_list);
		  $img_count = array();
		  for($i=1; $i <= count($img_list); $i++)
		  {
			$img_count[]= $i ;
		  }
		  $smarty->assign('img_count',   $img_count);
		}
		else
		{
		  $more_group = get_more_grouplist($city_id,$cat_id,1);
		  if (empty($more_group))
		  {
		    $url = rewrite_groupurl('subscribe.php');
            ecs_header("Location: $url\n");
            exit; 
		  }
		  $smarty->assign('more_group', $more_group);
		}
		  $smarty->assign('where', 'team');
		  $smarty->assign('comment_num',  get_group_comment_count($group_buy['group_id']));
		  $smarty->assign('group_comment', get_group_comment($group_buy['group_id']));
		  $smarty->assign('ask_url', rewrite_groupurl('ask.php',array('gid'=>$group_buy['group_id'])));
	      $smarty->assign('invite_url', rewrite_groupurl('invite.php',array('gid'=>$group_buy['group_id'])));
    }
    $smarty->display($mode_temp[$mode_id], $cache_id);
?>