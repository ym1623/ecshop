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
<ul class="usernav clearfix">
<li><a href="topman.php?type=seconds" <?php if ($this->_var['type'] == 'seconds'): ?>class="on"<?php endif; ?>>秒杀达人<span></span></a></li>
<li><a href="topman.php?type=invite" <?php if ($this->_var['type'] == 'invite'): ?>class="on"<?php endif; ?>>邀请达人<span></span></a></li>
<li><a href="topman.php?type=expense" <?php if ($this->_var['type'] == 'expense'): ?>class="on"<?php endif; ?>>消费达人<span></span></a></li>
<li><a href="topman.php?type=today" <?php if ($this->_var['type'] == 'today'): ?>class="on"<?php endif; ?>>今日达人<span></span></a></li>

</ul>
<div class="box-top2"></div>
<div class="sect">
<?php if ($this->_var['type'] == 'seconds'): ?>
<table class="topmanlist">
	<tr>
		<th width="50">排名</th>
		<th width="200">用户名</th>
		<th width="200">总共秒中</th>
		<th>今日秒中</th>
	</tr>
    <?php $_from = $this->_var['topmanlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['user']):
?>
	<tr>
		<td <?php if ($this->_var['key'] <= 3): ?>class="top"<?php endif; ?>><?php echo $this->_var['key']; ?></td>
		<td><?php echo $this->_var['user']['user_name']; ?></td>
		<td><?php echo $this->_var['user']['goods_number']; ?>件商品</td>
		<td><?php echo $this->_var['user']['today_number']; ?>件商品</td>
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php endif; ?>

<?php if ($this->_var['type'] == 'invite'): ?>
<table class="topmanlist">
	<tr>
		<th width="50">排名</th>
		<th width="200">用户名</th>
		<th width="200">邀请用户</th>
		<th>获得返利</th>
	</tr>
   <?php $_from = $this->_var['topmanlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['user']):
?>
	<tr>
		<td <?php if ($this->_var['key'] <= 3): ?>class="top"<?php endif; ?>><?php echo $this->_var['key']; ?></td>
		<td><?php echo $this->_var['user']['user_name']; ?></td>
		<td><?php echo $this->_var['user']['user_num']; ?></td>
		<td><?php echo $this->_var['user']['formated_all_rebate']; ?></td>
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

</table>
<?php endif; ?>

<?php if ($this->_var['type'] == 'expense'): ?>
<table class="topmanlist">
	<tr>
		<th width="50">排名</th>
		<th width="200">用户名</th>
		<th width="200">总购买</th>
		<th>共节省</th>
	</tr>
	 <?php $_from = $this->_var['topmanlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['user']):
?>
	<tr>
		<td <?php if ($this->_var['key'] <= 3): ?>class="top"<?php endif; ?>><?php echo $this->_var['key']; ?></td>
		<td><?php echo $this->_var['user']['user_name']; ?></td>
		<td><?php echo $this->_var['user']['goods_number']; ?>件商品</td>
		<td><?php echo $this->_var['user']['formated_save_amount']; ?></td>
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>

<?php endif; ?>
<?php if ($this->_var['type'] == 'today'): ?>
<table class="topmanlist">
<tr>
    <td colspan="4" style="text-align:left;"><strong>消费达人</strong></td>
</tr>
	<tr>
		<th width="50">排名</th>
		<th width="200">用户名</th>
		<th width="200">今日购买</th>
		<th>共节省</th>
	</tr>
	 <?php $_from = $this->_var['todaylist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['user']):
?>
	<tr>
		<td <?php if ($this->_var['key'] <= 3): ?>class="top"<?php endif; ?>><?php echo $this->_var['key']; ?></td>
		<td><?php echo $this->_var['user']['user_name']; ?></td>
		<td><?php echo $this->_var['user']['goods_number']; ?>件商品</td>
		<td><?php echo $this->_var['user']['formated_save_amount']; ?></td>
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<table class="topmanlist">
<tr>
    <td colspan="4" style="text-align:left;"><strong>邀请达人</strong></td>
</tr>
	<tr>
		<th width="50">排名</th>
		<th width="200">用户名</th>
		<th width="200">今日邀请</th>
		<th>获得返利</th>
	</tr>
	 <?php $_from = $this->_var['invitelist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'user');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['user']):
?>
	<tr>
		<td <?php if ($this->_var['key'] <= 3): ?>class="top"<?php endif; ?>><?php echo $this->_var['key']; ?></td>
		<td><?php echo $this->_var['user']['user_name']; ?></td>
		<td><?php echo $this->_var['user']['user_num']; ?>件商品</td>
		<td><?php echo $this->_var['user']['formated_all_rebate']; ?></td>
	</tr>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>


<?php endif; ?>

</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<?php echo $this->fetch('library/group_online.lbi'); ?>
<?php echo $this->fetch('library/group_vote.lbi'); ?>
</div>

</div>

</div>

<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>
</html>
