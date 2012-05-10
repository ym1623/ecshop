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
<div class="sect">
<?php if ($this->_var['action'] == 'get_password'): ?>
<h1 class="title2" style="margin-bottom:30px;">找回密码</h1>
<form name="formLogin" action="login.php" method="post" id="loginForm">
<table class="dataTab">
<tbody>
<tr>
<td align="right" width="200" height="40">用户名</td><td><input type="text" class="txt" name="user_name" id="user_name" size="40" /><span>输入您注册时的用户名</span></td>
</tr>
<tr>
<td align="right">Email</td><td><input type="text" name="email" class="txt" id="email" size="40" /><span>输入您注册时输入的Email，接收新密码！</span></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="hidden" value="send_pwd_email" name="act" />
<input type="submit" class="but" value="登陆" name="submit" /></td>
</tr>
</tbody>
</table>
</form>
<?php elseif ($this->_var['action'] == 'reset_password'): ?>
<h1 class="title2" style="margin-bottom:30px;">修改密码</h1>
<form name="formLogin" action="login.php" method="post" id="loginForm">
<table class="dataTab">
<tbody>
<tr>
<td align="right" width="200" height="40"><strong>密码</strong></td><td><input type="password" class="txt" name="new_password" id="new_password" size="40" /></td>
</tr>
<tr>
<td align="right"><strong>确认密码</strong></td><td><input type="password" name="confirm_password" class="txt" id="confirm_password" size="40" /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td> <input type="hidden" name="act" value="act_edit_password" />
            <input type="hidden" name="uid" value="<?php echo $this->_var['uid']; ?>" />
            <input type="hidden" name="code" value="<?php echo $this->_var['code']; ?>" />
<input type="submit" class="but" value="修改密码" name="submit" /></td>
</tr>
</tbody>
</table>
</form>
<?php else: ?>
<h1 class="title2" style="margin-bottom:30px;">马上登陆</h1>
<form name="formLogin" action="login.php" method="post" id="loginForm">
<table class="dataTab">
<tbody>
<tr>
<td align="right" width="200" height="40"><strong>Email／用户名</strong></td><td><input type="text" class="txt" name="username" size="40" /></td>
</tr>
<tr>
<td align="right"><strong>密&nbsp;&nbsp;码</strong></td><td><input type="password" name="password" class="txt" size="40" /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td style="font-size:12px;"><input type="checkbox" checked="" class="f-check" id="autologin" name="remember" value="1" /> 下次自动登录 | <a href="login.php?act=get_password">忘记密码？</a></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="hidden" value="act_login" name="act" />
<input type="submit" class="but" value="登陆" name="submit" /></td>
</tr>
</tbody>
</table>
</form>
<table class="dataTab" width="100%">
	<tr>
		<td>
<div class="onekey">
<?php $_from = $this->_var['loginopen']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'login');if (count($_from)):
    foreach ($_from AS $this->_var['login']):
?>	
<a target="_blank" href="<?php echo $this->_var['login']['web_login']; ?>"><img border="0" src="<?php echo $this->_var['login']['login_img']; ?>" /></a>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>
		</td>
	</tr>
</table>
<?php endif; ?>
</div>

<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock side-login">
<div class="sbox-top"></div>
<div class="sidemain">
<h1 class="title2">还没有<?php echo $this->_var['group_shopname']; ?>账户？</h1>
<p><a href="signup.php">立即注册</a> | 仅需30秒！</p>
</div>
<div class="sbox-bottom"></div>
</div>

</div>

</div>

</div>

<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>
</html>
