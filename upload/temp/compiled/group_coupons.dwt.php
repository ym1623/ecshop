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
<h1 class="title2">我的<?php echo $this->_var['group_cardname']; ?><span>分类：<a href="coupons.php"<?php if ($this->_var['action'] == 'coupons'): ?> class="on"<?php endif; ?>>未使用</a><a href="coupons.php?act=used"<?php if ($this->_var['action'] == 'used'): ?> class="on"<?php endif; ?>>已使用</a><a href="coupons.php?act=expired"<?php if ($this->_var['action'] == 'expired'): ?> class="on"<?php endif; ?>>已过期</a></span></h1>

<?php if ($this->_var['action'] == 'coupons'): ?>
<table class="carttable">
<tr>
<th height="40" width="120">团购项目</th>
<th>券编号</th>
<th>券密码</th>
<th>有效日期</th>
<th>操作</th>
</tr>

<?php $_from = $this->_var['coupons_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'coupons');if (count($_from)):
    foreach ($_from AS $this->_var['coupons']):
?>
<tr>
<td align="center">
<a target="_blank" href="<?php echo $this->_var['coupons']['group_url']; ?>"><img width="100" src="<?php echo $this->_var['coupons']['group_image']; ?>" /></a>
</td>
<td align="center" class="c1"><?php echo $this->_var['coupons']['card_sn']; ?></td>
<td align="center" class="c1"><?php echo $this->_var['coupons']['card_password']; ?></td>
<td align="center"><?php echo $this->_var['coupons']['end_date']; ?></td>
<td align="center"><a href="#" onclick="make_sms(<?php echo $this->_var['coupons']['card_id']; ?>);javascript:return false;">补发到手机</a>｜<a href="coupons.php?act=print&card_id=<?php echo $this->_var['coupons']['card_id']; ?>" target="_blank">打印</a>
</td>
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php endif; ?>

<?php if ($this->_var['action'] == 'used'): ?>
<table class="carttable">
<tr>
<th height="40" width="120">团购项目</th>
<th>券编号</th>
<th>消费日期</th>
</tr>

<?php $_from = $this->_var['coupons_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'coupons');if (count($_from)):
    foreach ($_from AS $this->_var['coupons']):
?>
<tr>
<td align="center"><a target="_blank" href="#"><img width="100" src="<?php echo $this->_var['coupons']['group_image']; ?>" /></a></td>
<td align="center"><?php echo $this->_var['coupons']['card_sn']; ?></td>
<td align="center"><?php echo $this->_var['coupons']['use_date']; ?></td>
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php endif; ?>

<?php if ($this->_var['action'] == 'expired'): ?>
<table class="carttable">
<tr>
<th height="40" width="120">团购项目</th>
<th>券编号</th>
<th>过期日期</th>
</tr>
<?php $_from = $this->_var['coupons_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'coupons');if (count($_from)):
    foreach ($_from AS $this->_var['coupons']):
?>
<tr>
<td align="center"><a target="_blank" href="#"><img width="100" src="<?php echo $this->_var['coupons']['group_image']; ?>" /></a></td>
<td align="center"><?php echo $this->_var['coupons']['card_sn']; ?></td>
<td align="center"><?php echo $this->_var['coupons']['end_date']; ?></td>
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

</table>
<?php endif; ?>
<div class="pages"><?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock uside-top">
<div class="sbox-top"></div>
<div class="sidemain">
<strong>什么是<?php echo $this->_var['group_cardname']; ?>？</strong>
<p><?php echo $this->_var['group_cardname']; ?>是当团购成功后，您以手机短信方式获取，或者自行下载打印使用的消费凭证（其中包含唯一优惠密码）。</p>
<strong>如何使用<?php echo $this->_var['group_cardname']; ?>？</strong>
<p>当您去消费时，出示该短信或者打印的<?php echo $this->_var['group_cardname']; ?>即可。打印版<?php echo $this->_var['group_cardname']; ?>上通常还包含更详细的使用说明。</p>
</div>
<div class="sbox-bottom"></div>
</div>

</div>

</div>

</div>
<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>
</html>