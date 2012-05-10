<?php if ($this->_var['today_group']): ?>
<div class="sideblock side-today" style="margin-top:0;">
<div class="sbox-top"></div>
<div class="sidemain">
<h1 class="title2">
<strong>
<?php if ($this->_var['group_buy']['activity_type'] == '1'): ?>
进行中的团购
<?php elseif ($this->_var['group_buy']['activity_type'] == '2'): ?>
进行中的秒杀
<?php else: ?>
热销商品
<?php endif; ?>
</strong></h1>
<ul>
<?php $_from = $this->_var['today_group']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'group');if (count($_from)):
    foreach ($_from AS $this->_var['group']):
?>
<li><a href="<?php echo $this->_var['group']['url']; ?>"><?php echo sub_str($this->_var['group']['group_name'],30); ?></a><a href="<?php echo $this->_var['group']['url']; ?>"><img width="210" src="<?php echo $this->_var['group']['group_image']; ?>" /></a>
<span class="rightPrice clearfix"><?php echo $this->_var['type_name']; ?>价：<em><?php echo $this->_var['group']['formated_group_price']; ?></em> | 已购：<em><?php 
$k = array (
  'name' => 'groupsaled',
  'group_id' => $this->_var['group']['group_id'],
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?></em> 人<a href="<?php echo $this->_var['group']['url']; ?>"><img width="54" src="template/meituan/images/min-buy.png" /></a></span></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>
<div class="sbox-bottom"></div>
</div>
<?php endif; ?>