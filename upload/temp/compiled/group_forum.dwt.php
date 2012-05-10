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
<ul class="usernav clearfix">
<li><a href="forum.php" <?php if ($this->_var['type'] == 'all'): ?>class="on"<?php endif; ?>>所有<span></span></a></li>
<li><a href="forum.php?type=city" <?php if ($this->_var['type'] == 'city'): ?>class="on"<?php endif; ?>><?php echo $this->_var['city_info']['city_name']; ?>讨论区<span></span></a></li>
<li><a href="forum.php?type=public" <?php if ($this->_var['type'] == 'public'): ?>class="on"<?php endif; ?>>公共讨论区<span></span></a></li>
<li><a href="forum.php?type=transferbuy" <?php if ($this->_var['type'] == 'transferbuy'): ?>class="on"<?php endif; ?>>转让求购区<span></span></a></li>
</ul>
<div class="box-top2"></div>
<div class="sect">
<?php if ($this->_var['act'] == 'list'): ?>
<?php if ($this->_var['type'] == 'transferbuy'): ?>
<h1 class="title2">求购转让<span class="add"><a href="forum.php?act=new&type=<?php echo $this->_var['type']; ?>">＋我要求购/转让</a></span></h1>
<?php else: ?>
<h1 class="title2">所有话题<span class="add"><a href="forum.php?act=new&type=<?php echo $this->_var['type']; ?>">＋发表新话题</a></span></h1>
<?php endif; ?>
<?php if ($this->_var['type'] == 'transferbuy'): ?>
<table class="forum-disc dataTab" width="100%">
<tr>
<th>标题</th>
<th>类型</th>
<th width="300">内容</th>
<th align="center">作者</th>
</tr>
<?php $_from = $this->_var['forumlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'forum');if (count($_from)):
    foreach ($_from AS $this->_var['forum']):
?>
<tr>
<td align="center"><?php echo $this->_var['forum']['forumtitle']; ?></td>
<td align="center"><?php if ($this->_var['forum']['forum_type'] == 1): ?>求购<?php else: ?>转让<?php endif; ?></td>
<td align="center"><?php echo $this->_var['forum']['forumcontent']; ?></td>
<td align="center"><font color="#399"><?php echo $this->_var['forum']['username']; ?><br />(<?php echo $this->_var['forum']['add_time']; ?>)</font></td>  
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php else: ?>
<table class="forum-disc dataTab" width="100%">
<tr>
<th>话题</th>
<?php if ($this->_var['type'] == 'public'): ?>
<th>讨论区</th>
<?php endif; ?>
<?php if ($this->_var['type'] == 'all'): ?>
<th>分类</th>
<?php endif; ?>
<th>回复/阅读</th>
<th align="left">最后发表</th>
</tr>
<?php $_from = $this->_var['forumlist']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'forum');if (count($_from)):
    foreach ($_from AS $this->_var['forum']):
?>
<tr>
<td><a href="<?php echo $this->_var['forum']['forum_url']; ?>" ><?php echo $this->_var['forum']['forumtitle']; ?></a></td>
<?php if ($this->_var['type'] == 'public'): ?>
<td><?php echo $this->_var['forum']['class_name']; ?></td>
<?php endif; ?>
<?php if ($this->_var['type'] == 'all'): ?>
<td align="center"><?php echo $this->_var['forum']['type']; ?></td>
<?php endif; ?>
<td align="center"><?php echo $this->_var['forum']['replay_num']; ?>/<?php echo $this->_var['forum']['click_num']; ?></td>
<td align="right" width="120"><font color="#399"><?php echo $this->_var['forum']['replay']['add_time']; ?></font></td>  
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<?php endif; ?>

<div class="pages">
<?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
<?php elseif ($this->_var['act'] == 'new'): ?>
<h1 class="title2">发表话题</h1>
<div class="askform">
<form name="formMsg" method="post" action="forum.php" id="formMsg">
<table class="forum-disc dataTab" width="100%">
<tbody>
<tr>
<td align="right" valign="top" width="90"><strong>讨论区</strong></td><td>
<select name="type_id">
  <optgroup label="本地讨论区">
    <option value="<?php echo $this->_var['city_info']['city_id']; ?>_0" <?php if ($this->_var['type'] == 'city'): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['city_info']['city_name']; ?>讨论区</option>
   </optgroup>
   <optgroup label="求购转让区">
    <option value="<?php echo $this->_var['city_info']['city_id']; ?>_0_1" <?php if ($this->_var['type'] == 'transferbuy'): ?> selected="selected"<?php endif; ?>>我要求购</option>
    <option value="<?php echo $this->_var['city_info']['city_id']; ?>_0_2">我要转让</option>
  </optgroup>
  <optgroup label="公共讨论区">
   <?php $_from = $this->_var['class_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'class');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['class']):
?>
    <option value="0_<?php echo $this->_var['class']['cid']; ?>" <?php if ($this->_var['type'] == 'public' && $this->_var['key'] == 0): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['class']['class_name']; ?></option>
   <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> 
 </optgroup>
</select></td>
</tr>
<tr>
<td align="right" valign="top"><strong>标题</strong></td><td><input type='text' name='forumtitle' class="txt" size="60" value='' /></td>
</tr>
<tr>
<td align="right" valign="top"><strong>内容</strong></td>
<td>
<textarea cols="90" class="area" style="height:60px;" name="forumcontent" id="seller_content"></textarea>
<p style="color:#666;">您最多输入110个汉字，还可以输入 <b><font color="#ff0000" id="num">110</font></b> 个字</p>
</td></tr>
<tr><td></td><td>
<input type="hidden" value="add_forum" name="act" />
<input type="hidden" value="<?php echo $this->_var['group_buy']['group_id']; ?>" name="id" />
<input type="submit" value="好了，提交" class="but" /></td>
</tr>
</tbody>
</table>
</form>
</div>
<?php endif; ?>

</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="uside-top"></div>
<?php echo $this->fetch('library/group_online.lbi'); ?>
<?php echo $this->fetch('library/group_vote.lbi'); ?>
</div>

</div>

</div>

<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>
</html>
