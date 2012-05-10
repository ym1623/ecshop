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
<link href="template/meituan/lightbox.css" rel="stylesheet" type="text/css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.min.js,jquery.lightbox.js,jquery-ecg.js')); ?>

</head>
<body>
<div id="box">
<?php echo $this->fetch('library/group_header.lbi'); ?>
<div class="mainbox clearfix">
<div class="maininfo lf">
<?php echo $this->fetch('library/group_user_menu.lbi'); ?>
<div class="box-top2"></div>
<div class="sect">
<h1 class="title2">我的订单</h1>
<table class="carttable">
<tr>
<th height="40">订单号</th>
<th>下单时间</th>
<th>订单总额 / 已付</th>
<th>状态</th>
<th>操作</th>
</tr>
<?php $_from = $this->_var['orders']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
<tr>
<td align="center"><a href="myorders.php?act=info&id=<?php echo $this->_var['item']['order_id']; ?>"><?php echo $this->_var['item']['order_sn']; ?></a></td>
<td align="center"><?php echo $this->_var['item']['order_time']; ?></td>
<td align="center"><strong><?php echo $this->_var['item']['formated_total_fee']; ?> / <?php echo $this->_var['item']['formated_pay_fee']; ?></strong></td>
<td align="center"><?php echo $this->_var['item']['order_status']; ?></td>
<td align="center"><?php echo $this->_var['item']['handler']; ?></td>
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<div class="pages"><?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock uside-top">
<div class="sbox-top"></div>
<div class="sidemain">
<strong>我已支付成功，为什么没有<?php echo $this->_var['group_cardname']; ?>？</strong>
<p>因为还没有到达最低团购人数，一旦凑够人数，您就会看到<?php echo $this->_var['group_cardname']; ?>了。</p>
<strong>什么是已过期订单？</strong>
<p>如果某个订单未及时付款，那么等团购结束时就无法再付款了，这种订单就是过期订单。</p>
</div>
<div class="sbox-bottom"></div>
</div>

</div>

</div>

</div>
<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>

</html>
