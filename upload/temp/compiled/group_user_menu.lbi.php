<ul class="usernav clearfix">
<li><a href="myorders.php" <?php if ($this->_var['menu'] == 'order'): ?>class="on"<?php endif; ?>>我的订单<span></span></a></li>
<li><a href="coupons.php" <?php if ($this->_var['menu'] == 'coupons'): ?>class="on"<?php endif; ?>>我的<?php echo $this->_var['group_cardname']; ?><span></span></a></li>
<li><a href="myask.php" <?php if ($this->_var['menu'] == 'myask'): ?>class="on"<?php endif; ?>>我的答疑<span></span></a></li>
<li><a href="account.php?act=credit" <?php if ($this->_var['menu'] == 'credit'): ?>class="on"<?php endif; ?>>账户余额<span></span></a></li>
<li><a href="account.php?act=settings" <?php if ($this->_var['menu'] == 'settings'): ?>class="on"<?php endif; ?>>账户设置<span></span></a></li>
<li><a href="account.php?act=address" <?php if ($this->_var['menu'] == 'address'): ?>class="on"<?php endif; ?>>配送地址<span></span></a></li>
</ul>
