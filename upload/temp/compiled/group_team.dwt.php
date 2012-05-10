<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="Generator" content="ECSHOP v2.7.3" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_var['city_info']['city_title']; ?></title>
<meta name="description" content="<?php echo $this->_var['city_info']['city_desc']; ?>" />
<meta name="keywords" content="<?php echo $this->_var['city_info']['city_keyword']; ?>" />


<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="animated_favicon.gif" type="image/gif" />
<link href="template/meituan/style.css" rel="stylesheet" type="text/css" />
<link href="template/meituan/slides.css" rel="stylesheet" type="text/css" />
<link href="template/meituan/lightbox.css" rel="stylesheet" type="text/css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.min.js,group.js,slides.js,jquery.lightbox.js,countdown.pack.js,jquery-ecg.js')); ?>
<script type=text/javascript>
$(function(){
if($(".slide-pic li").length > 1){
	slides();
	}
});
</script>
</head>
<body id="indexbody">
<div id="box">
<?php echo $this->fetch('library/group_header.lbi'); ?>
<div class="mainbox clearfix">
<?php 
$k = array (
  'name' => 'last_order',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
<div class="mainboxleft lf">
<?php echo $this->fetch('library/group_share.lbi'); ?>
<div id="todayteam" class="clearfix">
<h1 id="teamtitle"><span>今日<?php echo $this->_var['type_name']; ?>：</span><?php echo $this->_var['group_buy']['group_name']; ?></a></h1>
<?php 
$k = array (
  'name' => 'group_stats',
  'group_id' => $this->_var['group_buy']['group_id'],
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>

<div class="team-pic rf">

<div class="slides">
<ul class="slide-pic">
<?php $_from = $this->_var['img_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'img');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['img']):
?>
<?php if ($this->_var['key'] == '0'): ?>
<li><img alt="<?php echo $this->_var['img']['img_desc']; ?>" src="<?php echo $this->_var['img']['img_url']; ?>" /></li>
<?php else: ?>
<li style="display: none;"><img alt="<?php echo $this->_var['img']['img_desc']; ?>" src="<?php echo $this->_var['img']['img_url']; ?>" /></li>
<?php endif; ?>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<ul class="slide-li slide-txt op">
<?php $_from = $this->_var['img_count']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'id');if (count($_from)):
    foreach ($_from AS $this->_var['id']):
?>
<?php if ($this->_var['id'] == '1'): ?>
<li class="cur">1</li>
<?php else: ?>
<li><?php echo $this->_var['id']; ?></li>
<?php endif; ?>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>

<div class="oneview">
<?php echo $this->_var['group_buy']['small_desc']; ?>
</div>
</div>

</div>

<div class="maininfo">
<div class="box-top"></div>
<div class="infobox clearfix">
<div class="main lf">
<div id="xiangqing">
<?php echo $this->_var['group_buy']['group_desc']; ?>
</div>

<div id="friendsay">
<h1>网友们说</h1>
<ul>
<?php $_from = $this->_var['friend_comment']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment');if (count($_from)):
    foreach ($_from AS $this->_var['comment']):
?>
<li><?php echo $this->_var['comment']['friend_desc']; ?><span>——<?php echo $this->_var['comment']['friend_name']; ?>（<a href="<?php echo $this->_var['comment']['friend_url']; ?>" target="_blank"><?php echo $this->_var['comment']['friend_web']; ?></a>）</span></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>

</div>

<div class="shangjia rf">
<div class="store">
<h5><?php echo $this->_var['suppliers_arr']['suppliers_name']; ?></h5>
<div class="dizhi">
<?php if ($this->_var['suppliers_arr']['address'] != ''): ?>
<strong>地址：</strong><?php echo $this->_var['suppliers_arr']['address']; ?><br />
<?php endif; ?>
<?php if ($this->_var['suppliers_arr']['phone'] != ''): ?>
<strong>电话：</strong><?php echo $this->_var['suppliers_arr']['phone']; ?><br />
<?php endif; ?>
<?php if ($this->_var['suppliers_arr']['website'] != ''): ?>
<strong>网址：</strong><a href="<?php echo $this->_var['suppliers_arr']['website']; ?>" target="_blank" title="<?php echo $this->_var['suppliers_arr']['website']; ?>"><?php echo sub_str($this->_var['suppliers_arr']['website'],22); ?></a>
<?php endif; ?>
<?php if ($this->_var['suppliers_arr']['suppliers_desc'] != ''): ?>
<p><?php echo $this->_var['suppliers_arr']['suppliers_desc']; ?></p>
<?php endif; ?>
<?php if ($this->_var['suppliers_arr']['suppliers_area'] != ''): ?>
<div class="maps bor">
<?php echo $this->_var['suppliers_arr']['suppliers_area']; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php if ($this->_var['small_suppliers']): ?>
<?php $_from = $this->_var['small_suppliers']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'suppliers_arr_0_57890500_1336623097');if (count($_from)):
    foreach ($_from AS $this->_var['suppliers_arr_0_57890500_1336623097']):
?>
<div class="store">
<h5><?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['suppliers_name']; ?></h5>
<div class="dizhi">
<strong>地址：</strong><?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['address']; ?><br />
<strong>电话：</strong><?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['phone']; ?><br />
<strong>网址：</strong><a href="<?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['website']; ?>" target="_blank" title="<?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['website']; ?>"><?php echo sub_str($this->_var['suppliers_arr_0_57890500_1336623097']['website'],22); ?></a>
<p><?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['suppliers_desc']; ?></p>
<div class="maps bor">
<?php echo $this->_var['suppliers_arr_0_57890500_1336623097']['suppliers_area']; ?>
</div>
</div>
</div>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<?php endif; ?>
</div>

</div>

<div class="box-bottom clear"></div>
</div>

</div>
<div class="sidebox rf">
<?php echo $this->fetch('library/group_ing.lbi'); ?>
<?php echo $this->fetch('library/group_ask.lbi'); ?>
<?php echo $this->fetch('library/group_gonggao.lbi'); ?>
<div class="sideblock side-yaoqing">
<div class="sbox-top"></div>
<div class="sidemain">
<h1 class="title2">邀请有奖</h1>
<p>每邀请一位好友首次购买，您将获<span><?php echo $this->_var['group_buy']['formated_goods_rebate']; ?></span>元返利</p>
<a href="<?php echo $this->_var['invite_url']; ?>">» 点击获取您的专用邀请链接</a>
</div>
<div class="sbox-bottom"></div>
</div>

<?php echo $this->fetch('library/group_dream.lbi'); ?>
<?php echo $this->fetch('library/group_seller.lbi'); ?>
<?php echo $this->fetch('library/group_online.lbi'); ?>
<?php echo $this->fetch('library/group_vote.lbi'); ?>
</div>

</div>

</div>

<?php echo $this->fetch('library/group_footer.lbi'); ?>
<?php if ($this->_var['group_buy']['activity_type'] != '3'): ?>
<script type="text/javascript">
$(function(){
$("#leftTime_<?php echo $this->_var['group_buy']['group_id']; ?>").countdown({until:new Date(<?php 
$k = array (
  'name' => 'now_time',
  'end_date' => $this->_var['group_buy']['end_date'],
  'start_date' => $this->_var['group_buy']['start_date'],
  'act_type' => $this->_var['group_buy']['activity_type'],
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>),serverSync:serverTime});
});
//缓存服务器时间函数
function serverTime() { 
    var time = null; 
    $.ajax({url: 'serverTime.php', 
        async: false, dataType: 'text',
        success: function(text) { 
            time = new Date(text); 
        }, error: function(http, message, exc) { 
            time = new Date(); 
    }});
    return time; 
}
</script>
<?php endif; ?>
</body>

</html>