<script type="text/javascript">
var city_name = '<?php echo $this->_var['city_info']['city_name']; ?>';
</script>
<!--[if lte IE 6]>
<script src="js/iepngfix_tilebg.js" type="text/javascript"></script>
<![endif]-->
<div id="header">
<div id="top">
<a href="<?php echo $this->_var['index_url']; ?>"><img class="logo" src="template/meituan/images/logo.png" /></a>
<div id="city" class="lf">
<?php echo $this->_var['city_info']['city_name']; ?><br />
<a href="#" class="thiscity" onclick="openHtml('.thiscity','#evercity','480','选择所在城市');return false;">[切换城市]</a>
<div class="disnone">
	<div id="evercity" >
	<?php $_from = $this->_var['group_city']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
	<a href="<?php echo $this->_var['city']['url']; ?>"<?php if ($this->_var['city']['city_id'] == $this->_var['cityid']): ?> class="on"<?php endif; ?>><?php echo $this->_var['city']['city_name']; ?></a>
	<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</div>
</div>
</div>
<div class="rss">
<p>想知道明天的团购是什么吗？</p>
<form id="validator" method="post" action="subscribe.php">
<input type="text" id="email" datatype="email" require="true" size="20" value="" name="email" />
<select name="city_id" class="lf">
<?php $_from = $this->_var['group_city']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
	<option value="<?php echo $this->_var['city']['city_id']; ?>"<?php if ($this->_var['city']['city_id'] == $this->_var['cityid']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['city']['city_name']; ?></option>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</select>
<input type="hidden" value="add_email" name="act" />
<input type="hidden" value="add" name="do" />
<input type="image" value="订阅" src="template/meituan/images/but1.gif">
</form>
<p><a href="#" rel="addrss" class="phonerss" onclick="javascript:return false">» 短信订阅，免费！</a>  <a href="#" rel="delrss" class="phonerss" onclick="javascript:return false">» 取消手机订阅</a></p>
</div>
</div>
<div id="nav">
<ul class="lf">
<?php $_from = $this->_var['navigation']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('nav', 'navdesc');if (count($_from)):
    foreach ($_from AS $this->_var['nav'] => $this->_var['navdesc']):
?>
<li<?php if ($this->_var['where'] == $this->_var['nav']): ?> class="on"<?php endif; ?>><a href="<?php echo $this->_var['navdesc']['url']; ?>"><?php echo $this->_var['navdesc']['name']; ?></a></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
<div id="menubox" class="userbut rf">
<?php 
$k = array (
  'name' => 'group_member_info',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
</div>
</div>
</div>
