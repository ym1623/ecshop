<?php if ($this->_var['user_info']): ?>
<div id="menulist">
	<a class="top" href="myorders.php"><?php echo $this->_var['user_info']['username']; ?>的会员中心</a>
	<div id="ml">
	<div class="ml-top"></div>
	<div class="ml-bottom">
	<span><a href="myorders.php">我的订单</a></span>
	<span><a href="coupons.php">我的团购券</a></span>
	<span><a href="myask.php">我的答疑</a></span>
	<span><a href="account.php?act=credit">账户余额</a></span>
	<span><a href="account.php?act=settings">账户设置</a></span>
	<span><a href="account.php?act=address">配送地址</a></span>
	<span><a href="account.php?act=logout">退出</a></span>
	</div>
	</div>
</div>
<?php else: ?>
<div class="upbut"><a href="login.php">登陆</a> | <a href="signup.php">注册</a></div>
<?php endif; ?>