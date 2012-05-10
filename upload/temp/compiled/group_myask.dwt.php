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
<h1 class="title2">我的答疑</h1>
<ul class="asklist">
<?php $_from = $this->_var['comments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment');if (count($_from)):
    foreach ($_from AS $this->_var['comment']):
?>             
<li>
<p class="user">
<strong>
<a href="<?php echo $this->_var['comment']['group_url']; ?>">团购项目：<?php echo sub_str($this->_var['comment']['group_name'],30); ?></a></strong><span><?php echo $this->_var['comment']['end_time']; ?></span>
</p>
<p class="text"><?php echo $this->_var['comment']['content']; ?></p>
  <?php if ($this->_var['comment']['re_content']): ?> 
<p class="reply"><strong>回复：</strong><?php echo $this->_var['comment']['re_content']; ?></p>
 <?php endif; ?>
</li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>

<div class="pages"><?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock uside-top">
<div class="sbox-top"></div>
<div class="sidemain">
<strong>更方便的查看您所有的留言！</strong>
<p>这里包括您在<?php echo $this->_var['group_shopname']; ?>上所有的留言内容，您可以方便的管理留言及查看回复状态。</p>
</div>
<div class="sbox-bottom"></div>
</div>

</div>

</div>

</div>
<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>

</html>
