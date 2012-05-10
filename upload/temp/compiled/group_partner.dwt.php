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
<?php if ($this->_var['class_list']): ?>
<ul class="usernav clearfix">
<li><a href="<?php echo $this->_var['class_url']; ?>" <?php if ($this->_var['catid'] == '0'): ?>class="on"<?php endif; ?>>所有<span></span></a></li>
<?php $_from = $this->_var['class_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'class');if (count($_from)):
    foreach ($_from AS $this->_var['class']):
?>
<li><a href="<?php echo $this->_var['class']['url']; ?>" <?php if ($this->_var['class']['cid'] == $this->_var['catid']): ?>class="on"<?php endif; ?>><?php echo $this->_var['class']['class_name']; ?><span></span></a></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<?php endif; ?>
<div class="box-top2"></div>
<div class="sect">
<h1 class="title2">品牌商户</h1>
<ul id="deals-list" class="clearfix">
<?php $_from = $this->_var['suppliers_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'suppliers');if (count($_from)):
    foreach ($_from AS $this->_var['suppliers']):
?>
<li class="par-list">
<h4><a href="<?php echo $this->_var['suppliers']['url']; ?>"><?php echo htmlspecialchars($this->_var['suppliers']['suppliers_name']); ?></a></h4>
<a href="<?php echo $this->_var['suppliers']['url']; ?>" class="thumb"><img src="<?php echo $this->_var['suppliers']['spread_img']; ?>" border="0"  width="200" height="121" alt="<?php echo htmlspecialchars($this->_var['group_buy']['group_name']); ?>" /></a>
<div class="info">参团：<strong><?php echo $this->_var['suppliers']['group_num']; ?></strong> 次<br>让利：<?php echo $this->_var['suppliers']['formated_save_amount']; ?><br>购买：<strong><?php echo $this->_var['suppliers']['person_num']; ?></strong> 人次<p>电话：<?php echo $this->_var['suppliers']['phone']; ?></p></div>
</li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
</ul>

<div class="pages"><?php echo $this->fetch('library/group_pages.lbi'); ?></div>
</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<?php echo $this->fetch('library/group_seller.lbi'); ?>
<?php echo $this->fetch('library/group_online.lbi'); ?>
</div>

</div>
</div>
<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>
</html>
