<div class="team-about lf">
<?php if ($this->_var['group_buy']['status'] == '1'): ?>

<div class="deal-discount bor">
    <form action="buy.php" method="post" name="ecgroupon_frm" id="ecgroupon_frm">
<p class="deal-price"><strong>
<?php echo $this->_var['group_buy']['formated_group_price']; ?></strong><span> <input type="image"  src="template/meituan/images/buy.png" id="group_butn"></span></p>
 <input name="number" type="hidden"  id="number" value="1" size="4" />
  <input name="a" type="hidden"  id="a" value="buy" size="4" />
  <input type="hidden" name="group_id" value="<?php echo $this->_var['group_buy']['group_id']; ?>" />
</form>
<table width="100%">
<tr><th>原价</th><th>折扣</th><th>节省</th></tr>
<tr><td><?php echo $this->_var['group_buy']['formated_market_price']; ?></td><td><?php echo $this->_var['group_buy']['group_rebate']; ?>折</td><td><?php echo $this->_var['group_buy']['formated_lack_price']; ?></td></tr>
</table>
</div>
<div class="deal-status">
<p><em><?php echo $this->_var['group_buy']['orders_num']; ?></em> <strong>人已购买</strong></p>
<p>数量有限，下单要快哦</p>
<?php if ($this->_var['is_succes'] == '1'): ?>
<p class="deal-buy-on"><img src="template/meituan/images/start.gif" /><strong>团购成功！继续购买</strong></p>
<?php else: ?>
<p class="deal-buy-on"><img src="template/meituan/images/start.gif" /><strong>团购已经开始，正在进行！</strong></p>
<?php endif; ?>
<p class="time">距离结束：<em><span class="time" id="leftTime_<?php echo $this->_var['group_buy']['group_id']; ?>"><?php echo $this->_var['lang']['please_waiting']; ?></span>
</em></p>
<?php if ($this->_var['group_buy']['succeed_time'] > '0'): ?>
<p><?php echo $this->_var['group_buy']['succeed_time_date']; ?>达到最低团购人数：<?php echo $this->_var['group_buy']['lower_orders']; ?> 人</p>
<?php endif; ?>
</div>

<?php elseif ($this->_var['group_buy']['status'] == '2'): ?>

<div class="deal-discount bor">
<p class="deal-price"><strong><?php echo $this->_var['group_buy']['formated_group_price']; ?></strong><span><a href="#"><img src="template/meituan/images/but-over.gif"></a></span></p>
<table width="100%">
<tr><th>原价</th><th>折扣</th><th>节省</th></tr>
<tr><td><?php echo $this->_var['group_buy']['formated_market_price']; ?></td><td><?php echo $this->_var['group_buy']['group_rebate']; ?>折</td><td><?php echo $this->_var['group_buy']['formated_lack_price']; ?></td></tr>
</table>
</div>
<div class="deal-status">
<p><em><?php echo $this->_var['group_buy']['orders_num']; ?></em> <strong>人已购买</strong></p>
<p class="over"><img src="template/meituan/images/over.gif" /></p>
<p class="time">本团结束于：<em><?php echo $this->_var['group_buy']['closed_time_date']; ?></em></p>
<?php if ($this->_var['group_buy']['succeed_time'] > '0'): ?>
<p><?php echo $this->_var['group_buy']['succeed_time_date']; ?>达到最低团购人数：<?php echo $this->_var['group_buy']['lower_orders']; ?> 人</p>
<?php endif; ?>
</div>

<?php elseif ($this->_var['group_buy']['status'] == '5'): ?>

<div class="deal-discount bor">
<p class="deal-price"><strong><?php echo $this->_var['group_buy']['formated_group_price']; ?></strong><span><a href="#"><img src="template/meituan/images/but-over.gif"></a></span></p>
<table width="100%">
<tr><th>原价</th><th>折扣</th><th>节省</th></tr>
<tr><td><?php echo $this->_var['group_buy']['formated_market_price']; ?></td><td><?php echo $this->_var['group_buy']['group_rebate']; ?>折</td><td><?php echo $this->_var['group_buy']['formated_lack_price']; ?></td></tr>
</table>
</div>
<div class="deal-status">
<p><em><?php echo $this->_var['group_buy']['orders_num']; ?></em> <strong>人已购买</strong></p>
<p class="over"><img src="template/meituan/images/kong.gif" /></p>
<p class="time">本团结束于：<em><?php echo $this->_var['group_buy']['closed_time_date']; ?></em></p>
<?php if ($this->_var['group_buy']['succeed_time'] > '0'): ?>
<p><?php echo $this->_var['group_buy']['succeed_time_date']; ?>达到最低团购人数：<?php echo $this->_var['group_buy']['lower_orders']; ?> 人</p>
<?php endif; ?>
</div>

<?php elseif ($this->_var['group_buy']['status'] == '0'): ?>

<div class="deal-discount bor">
<p class="deal-price"><strong><?php echo $this->_var['group_buy']['formated_group_price']; ?></strong><span><a href="#"><img src="template/meituan/images/but-over.gif"></a></span></p>
<table width="100%">
<tr><th>原价</th><th>折扣</th><th>节省</th></tr>
<tr><td><?php echo $this->_var['group_buy']['formated_market_price']; ?></td><td><?php echo $this->_var['group_buy']['group_rebate']; ?>折</td><td><?php echo $this->_var['group_buy']['formated_lack_price']; ?></td></tr>
</table>
</div>
<div class="deal-status">
<p><em><?php echo $this->_var['group_buy']['orders_num']; ?></em> <strong>人已购买</strong></p>
<p>数量有限，下单要快哦</p>
<p class="time">距离结束：<em><span>还未开始</span></em></p>
</div>

<?php elseif ($this->_var['group_buy']['status'] == '4'): ?>

<div class="deal-discount bor">
<p class="deal-price"><strong><?php echo $this->_var['group_buy']['formated_group_price']; ?></strong><span><a href="#"><img src="template/meituan/images/but-over.gif"></a></span></p>
<table width="100%">
<tr><th>原价</th><th>折扣</th><th>节省</th></tr>
<tr><td><?php echo $this->_var['group_buy']['formated_market_price']; ?></td><td><?php echo $this->_var['group_buy']['group_rebate']; ?>折</td><td><?php echo $this->_var['group_buy']['formated_lack_price']; ?></td></tr>
</table>
</div>
<div class="deal-status">
<p><em><?php echo $this->_var['group_buy']['orders_num']; ?></em> <strong>人已购买</strong></p>
<p class="over"><img src="template/meituan/images/over-sb.gif" /></p>
<p class="time">本团结束于：<em><?php echo $this->_var['group_buy']['closed_time_date']; ?></em></p>
</div>

<?php endif; ?>
</div>
