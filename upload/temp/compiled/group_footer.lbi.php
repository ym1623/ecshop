
<div class="footer clear">
<?php if ($this->_var['txt_links']): ?>
<div class="flink">
<table width="100%">
	<tr>
		<td align="left" height="54"><strong>友情链接：</strong>
    <?php if ($this->_var['txt_links']): ?>
    <?php $_from = $this->_var['txt_links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
    <a href="<?php echo $this->_var['link']['url']; ?>" target="_blank" title="<?php echo $this->_var['link']['name']; ?>"><?php echo $this->_var['link']['name']; ?></a>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?> <a href="links.php" target="_blank">更多>></a>
    <?php endif; ?></td>
		<td align="right" width="130"><strong>ECGROUPON 3.0</strong></td>
	</tr>
</table>
</div>
<?php endif; ?>
<ul class="subnav clearfix">
<?php $_from = $this->_var['group_help']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'help');if (count($_from)):
    foreach ($_from AS $this->_var['help']):
?>
<li>
<h3><?php echo $this->_var['help']['cat_name']; ?></h3>
<?php $_from = $this->_var['help']['article']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'article');if (count($_from)):
    foreach ($_from AS $this->_var['article']):
?> 
<a href="<?php echo $this->_var['article']['url']; ?>"><?php echo $this->_var['article']['title']; ?></a>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
<li class="end">
<a href="<?php echo $this->_var['index_url']; ?>"><img src="template/meituan/images/logo.png" width="200" /></a>
</li>
</ul>
<div class="copyright"></div>
</div>
