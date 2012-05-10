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
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.min.js,jquery.validate.pack.js,jquery.lightbox.js,jquery-ecg.js')); ?>
</head>
<body>
<div id="box">
<?php echo $this->fetch('library/group_header.lbi'); ?>
<div class="mainbox clearfix">
<div class="maininfo lf">
<div class="box-top2"></div>
<div class="welcome">
<h1 class="title2">邮件订阅</h1>
<div class="rssbox">
<h4>把<?php echo $this->_var['city_info']['city_name']; ?>每天最新的精品团购信息发到您的邮箱。</h4>
<div class="enter-address">
<p>立即邮件订阅每日团购信息，不错过每一天的惊喜。</p>
<form class="validator" method="post" action="subscribe.php" id="rssform" name="rssform">
<table class="dataTab" width="100%">
<tr><td><strong>邮件地址：</strong>
<input type="text" datatype="email" require="true" size="60" value="" class="txt" name="email" id="email"/>
<select class="city" name="city_id">
<?php $_from = $this->_var['group_city']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
  <option <?php if ($this->_var['city']['city_id'] == $this->_var['cityid']): ?>selected="selected"<?php endif; ?> value="<?php echo $this->_var['city']['city_id']; ?>"><?php echo $this->_var['city']['city_name']; ?></option>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</select>
<input type="hidden" value="add_email" name="act" />
<input type="hidden" value="add" name="do" />
<input type="submit" value="订阅" class="but">
</td></tr>
</table>
</form>
<span class="tip">请放心，我们和您一样讨厌垃圾邮件</span>
</div>
<div class="intro">
<p>每日精品团购包括：</p>
<p>餐厅、酒吧、KTV、SPA、美发、健身、瑜伽、演出、影院等。</p>
</div>
</div>

</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock zs-box">
<div class="sbox-top"></div>
<div class="sidemain" style="padding:10px 8px 10px 7px;text-align:center;">
<img src="template/meituan/images/zs-1.jpg" />
<img src="template/meituan/images/zs-2.jpg" />
<img src="template/meituan/images/zs-3.jpg" />
</div>
<div class="sbox-bottom"></div>
</div>
</div>

</div>

</div>
<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>

</html>
