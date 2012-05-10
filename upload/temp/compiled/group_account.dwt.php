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
<?php echo $this->smarty_insert_scripts(array('files'=>'jquery.min.js,jquery.validate.pack.js,group_region.js,jquery.lightbox.js,jquery-ecg.js')); ?>
</head>
<body>
<div id="box">
<?php echo $this->fetch('library/group_header.lbi'); ?>
<div class="mainbox clearfix">
<div class="maininfo lf">
<?php echo $this->fetch('library/group_user_menu.lbi'); ?>
<div class="box-top2"></div>
<?php if ($this->_var['action'] == 'credit'): ?>
<div class="sect">
<h1 class="title2">帐户余额</h1>
<div style="padding:10px 20px;width:90%;margin:15px auto;border:1px solid #FFEC19;background:#FFFBCC;">充值到<?php echo $this->_var['group_shopname']; ?>帐户，方便抢购！<a href="account.php?act=charge"> » 立即充值</a></div>
<p class="dnum">您的账户余额是：<strong style="font-size:24px;font-family:Helvetica,Arial,sans-serif;"><?php echo $this->_var['surplus_amount']; ?></strong> 元</p>
<table class="carttable">
<tr>
<th height="40" width="120">时间</th>
<th>详情</th>
<th>收支</th>
<th>金额(元)</th>
</tr>

<?php $_from = $this->_var['account_log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
<tr>
<td align="center"><?php echo $this->_var['item']['change_time']; ?></td>
<td><?php echo $this->_var['item']['short_change_desc']; ?></td>
<td align="center"><?php echo $this->_var['item']['type']; ?></td>
<td align="center"><?php echo $this->_var['item']['amount']; ?></td>
</tr>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>

</table>
<div class="pages">
<?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
</div>

<?php elseif ($this->_var['action'] == 'settings'): ?>
<div class="sect">
<h1 class="title2" style="margin-bottom:20px;">帐户设置</h1>
<form name="formEdit" action="account.php" method="post" id="loginForm">
<table class="dataTab" width="100%">
<tr>
<td align="right" valign="top" width="120"><strong>Email</strong></td>
<td>
<input type="text" value="<?php echo $this->_var['profile']['email']; ?>" class="txt" name="email" disabled="disabled" size="60" /><span>请输入有效的Email地址，登录及找回密码用，不会公开</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>用户名</strong></td>
<td><input type="text" value="<?php echo $this->_var['profile']['user_name']; ?>" class="txt" disabled="disabled" size="60" /><span>4-16 个字符，一个汉字为两个字符</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>密码</strong></td>
<td>
<input type="password" class="txt" name="new_password" id="new_password" size="60" /><span>最少 4 个字符</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>确认密码</strong></td>
<td>
<input type="password" class="txt" name="confirm_password" id="confirm_password" size="60" /><span>请重复输入密码</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>手机号码</strong></td>
<td>
<input type="text" class="txt" name="mobile_phone" value="<?php echo $this->_var['profile']['mobile_phone']; ?>" id="mobile_phone" /><span>手机号码是我们联系你的最重要的联系方式，并用于<?php echo $this->_var['group_cardname']; ?>的短信通知</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>所在城市</strong></td>
<td>
<select class="f-city" name="city_id">
<?php $_from = $this->_var['group_city']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
<option value="<?php echo $this->_var['city']['city_id']; ?>"<?php if ($this->_var['city']['city_id'] == $this->_var['profile']['city_id']): ?> selected="selected"<?php endif; ?>><?php echo $this->_var['city']['city_name']; ?></option>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</select>
</td>
</tr>
<tr>
<td></td>
<td>
<input type="checkbox" checked="checked" class="f-check" />
<input name="act" type="hidden" value="act_settings" />
订阅每日最新团购信息<br /><input type="submit" class="but" value="修改" />
</td>
</tr>
</table>
</form>
</div>
<?php elseif ($this->_var['action'] == 'address'): ?>
<div class="sect">
<h1 class="title2" style="margin-bottom:20px;">配送地址</h1>
<form name="formEdit" action="account.php" method="post" id="group_address">
<table class="dataTab" width="100%">
<tr>
<td align="right" valign="top" width="120"><strong>收件人</strong></td>
<td>
<input type="text" value="<?php echo $this->_var['consignee']['consignee']; ?>" name="consignee" class="txt" size="60" id="consignee" /><span>这里填写您的真实姓名，仅作接收快递时使用。</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>手机号</strong></td>
<td><input type="text" value="<?php echo $this->_var['consignee']['mobile']; ?>" name="mobile" class="txt" size="20" /><span>手机号码是我们联系你的最重要的联系方式</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>省市区</strong></td>
<td>
   <select name="country" id="selCountries" onchange="region.changed(this, 1, 'selProvinces')" style="border:1px solid #ccc;">
        <option value="0">请选择国家</option>
        <?php $_from = $this->_var['country_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'country');if (count($_from)):
    foreach ($_from AS $this->_var['country']):
?>
        <option value="<?php echo $this->_var['country']['region_id']; ?>" <?php if ($this->_var['consignee']['country'] == $this->_var['country']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['country']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <select name="province" id="selProvinces" onchange="region.changed(this, 2, 'selCities')" style="border:1px solid #ccc;">
        <option value="0">请选择省区</option>
        <?php $_from = $this->_var['province_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'province');if (count($_from)):
    foreach ($_from AS $this->_var['province']):
?>
        <option value="<?php echo $this->_var['province']['region_id']; ?>" <?php if ($this->_var['consignee']['province'] == $this->_var['province']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['province']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <select name="city" id="selCities" onchange="region.changed(this, 3, 'selDistricts')" style="border:1px solid #ccc;">
        <option value="0">请选择城市</option>
        <?php $_from = $this->_var['city_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'city');if (count($_from)):
    foreach ($_from AS $this->_var['city']):
?>
        <option value="<?php echo $this->_var['city']['region_id']; ?>" <?php if ($this->_var['consignee']['city'] == $this->_var['city']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['city']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select>
      <select name="district" id="selDistricts" <?php if ($this->_var['consignee']['district'] <= '0'): ?>style="display:none"<?php endif; ?> onchange="region.set_address()">
        <option value="0">请选择市区</option>
                <?php $_from = $this->_var['district_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'district');if (count($_from)):
    foreach ($_from AS $this->_var['district']):
?>
        <option value="<?php echo $this->_var['district']['region_id']; ?>" <?php if ($this->_var['consignee']['district'] == $this->_var['district']['region_id']): ?>selected<?php endif; ?>><?php echo $this->_var['district']['region_name']; ?></option>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </select></td>
</tr>
<tr>
<td align="right" valign="top"><strong>收件地址</strong></td>
<td>
<input type="text" class="txt" size="60" name="address" value="<?php echo $this->_var['consignee']['address']; ?>" id="address" /><span>选择地区并补全地址</span>
</td>
</tr>
<tr>
<td align="right" valign="top"><strong>邮政编码</strong></td>
<td>
<input type="text" class="txt" name="zipcode" value="<?php echo $this->_var['consignee']['zipcode']; ?>" id="zipcode" /></td>
</tr>
<tr>
<td></td>
<td>
<input name="act" type="hidden" value="act_address" />
  <input type="hidden" name="address_id" value="<?php echo $this->_var['consignee']['address_id']; ?>" />
<input type="submit" class="but" value="修改" />
</td>
</tr>
</table>
</form>
</div>
<?php elseif ($this->_var['action'] == 'charge'): ?>
<div class="sect">
<h1 class="title2" style="margin-bottom:20px;">账户充值</h1>
<form action="#" method="post" id="form_amount">
<table class="dataTab" style="width:90%;margin:0 auto;">
<tr>
<td width="120"><strong>请输入充值金额：</strong></td>
<td>
<input type="text" name="amount"  class="txt" value="<?php echo $this->_var['order']['amount']; ?>" size="30" id="amount" /></td>
</tr>
<tr>
<td></td>
<td>
<?php $_from = $this->_var['payment']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
<input type="radio" name="payment_id" value="<?php echo $this->_var['list']['pay_id']; ?>" <?php if ($this->_var['pid'] == $this->_var['list']['pay_id']): ?>checked="checked"<?php endif; ?> /> <?php echo $this->_var['list']['pay_name']; ?>&nbsp;&nbsp;
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</td></tr>
<tr>
  <td></td>
  <td>
  <input type="hidden" name="rec_id" value="<?php echo $this->_var['order']['id']; ?>" />
  <input type="hidden" name="act" value="act_charge" />
  <input type="submit" class="but" value="确定，去充值" />
  </td>
</tr>
</table>
</form>
<?php if ($this->_var['account_log']): ?>
<h1 class="title2">充值记录</h1>
<table class="carttable">
<tr>
<th height="40" width="120">操作时间</th>
<th>金额(元)</th>
<th>管理员备注</th>
<th>支付方式</th>
<th>付款状态</th>
<th>操作</th>
</tr>
 <?php $_from = $this->_var['account_log']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
          <tr>
            <td align="center" ><?php echo $this->_var['item']['add_time']; ?></td>
            <td align="center"><?php echo $this->_var['item']['amount']; ?></td>
            <td align="center"><?php echo $this->_var['item']['short_admin_note']; ?></td>
            <td align="center"><?php echo $this->_var['item']['payment']; ?></td>
            <td align="center"><?php echo $this->_var['item']['pay_status']; ?></td>
            <td align="center" ><?php echo $this->_var['item']['handle']; ?>
              <?php if (( $this->_var['item']['is_paid'] == 0 ) || $this->_var['item']['handle']): ?>
              <a href="account.php?act=cancel&id=<?php echo $this->_var['item']['id']; ?>" onclick="if (!confirm('<?php echo $this->_var['confirm_remove_account']; ?>')) return false;"><?php echo $this->_var['lang']['is_cancel']; ?></a>
              <?php endif; ?>
			</td>
          </tr>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</table>
<div class="pages">
<?php echo $this->fetch('library/group_pages.lbi'); ?>
</div>
<?php endif; ?>
</div>
<?php elseif ($this->_var['action'] == 'act_charge'): ?>
<div class="sect">
<h1 class="title2">充值金额：<strong style="font-size:24px;color:#f00;"><?php echo $this->_var['order']['order_amount']; ?></strong> 元</h1>

<div class="minfo" style="text-align:center;"><br />
<?php if ($this->_var['payment']['pay_button']): ?><?php echo $this->_var['payment']['pay_button']; ?><?php else: ?><?php echo $this->_var['payment']['pay_desc']; ?><?php endif; ?>
<a href="account.php?act=pay&id=<?php echo $this->_var['order']['order_sn']; ?>" style="font-size:12px;color:#6C6C6C;margin-top:5px;display:block;">» 返回选择其他支付方式</a></div>
</div>
<?php endif; ?>


<div class="box-bottom2"></div>
</div>

<div class="sidebox rf">
<div class="sideblock uside-top">
<div class="sbox-top"></div>
<div class="sidemain">
<strong>什么是账户余额？</strong>
<p>账户余额是你在<?php echo $this->_var['group_shopname']; ?>团购时可用于支付的金额。</p>
<strong>可以往账户里充值么？</strong>
<p>请到<a href="account.php?act=credit">账户余额</a>菜单，在线充值。</p>
<strong>那怎样才能有余额？</strong>
<p>邀请好友获得返利将充值到账户余额，参加团购亦可获得返利。</p>
</div>
<div class="sbox-bottom"></div>
</div>

</div>

</div>

</div>

<?php echo $this->fetch('library/group_footer.lbi'); ?>
</body>

</html>
