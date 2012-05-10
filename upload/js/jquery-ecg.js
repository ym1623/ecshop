/* 
以下脚本基于jquery编写;
如果需要插件支持，将在注释中说明;
http://www.ecgroupon.com/
ECGroupon团队
 */
//手机订阅更换验证码
function change_captcha(obj)
{
 	obj.src = 'captcha.php?'+Math.random();
}
//页面内部html弹出
function openHtml(thisTag,openTagId,boxWidth,boxTitle){
	//当前对象class、被弹出的层ID、弹出框宽度、弹出框标题
	$(thisTag).colorbox({transition:"none", width:boxWidth, inline:true, href:openTagId, title:boxTitle});
}
//补发优惠券短信
function make_sms(card_id)
{
	if(card_id > 0)
	{
	  $.ajax({
		   url:"coupons.php?act=make_sms",
		   type:"POST",
		   data:'card_id=' + card_id,
		   cache: false,
		   dataType: 'json',
		   success:open_smshtml
		   });
	}
}
function open_smshtml(divtxt)
{
   $str = '重新发送到手机';
   $html_txt = divtxt;
   $.colorbox({transition:"none", width:420, html:$html_txt, title:$str});	
}
function send_sms()
{
  	 var mobile = $('#mobile').val();
     var card_id = $('#card_id').val();
	 $.ajax({
		   url:"coupons.php?act=send_sms",
		   type:"POST",
		   data:'card_id=' + card_id +'&mobile=' + mobile,
		   cache: false,
		   dataType: 'json',
		   success:make_smsResponse
		   });
}
//消费券验证
function set_card(){
    var card_sn = $("#card_sn").attr('value');
	var card_pass = $("#card_pass").attr('value');
    var suid = $("#suid").attr('value');
	var otherdata = '';
	if (suid >= 0)
	{
	  otherdata = '&suid=' + suid;
	}
	if (card_sn != '' && card_pass != '')
	{
	  $.ajax({
		   url:"merchant.php?act=set_coupons",
		   type:"POST",
		   data:'card_sn=' + card_sn + '&card_pass=' + card_pass + otherdata,
		   cache: false,
		   dataType: 'json',
		   success:make_smsResponse
		   });
	}
	return false;
}
//商户自行处理发货
function deli_order(ordersn)
{
 $('#ordersn').attr("value", ordersn);	
 openHtml('.openInvoice','#setInvoice','380','发货处理');
}
function shipments()
{
  var ordersn = $("#ordersn").attr('value');
  var invoice = $("#invoice").attr('value');
  $.ajax({
		   url:"merchant.php?act=delivery",
		   type:"POST",
		   data:'ordersn=' + ordersn + '&invoice='+invoice,
		   cache: false,
		   dataType: 'json',
		   success:make_smsResponse
		   });
	
	return false;
}
//显示错误提示
function make_smsResponse(sms_txt)
{
	if($("#tip").length > 0){
		$("#tip span").html(sms_txt);
	}else{
		alert (sms_txt);
	}
  return false;
}
//手机订阅
function phoneRss(doState){
	var phone = $("#phoneRssWrap #phone").attr('value');
	var captcha = $("#phoneRssWrap #captcha").attr('value');
	var phone_hash = $("#phoneRssWrap #phone_hash").attr('value');
	if (phone != '' && captcha != '')
	{
	  $.ajax({
		   url:"phone.php?act=add_phone",
		   type:"POST",
		   data:'phone=' + phone + '&captcha=' + captcha + '&doi=' + doState,
		   cache: false,
		   dataType: 'json',
		   success:phone_Response
		   });
	}
	return false;
}
//手机订阅
function check_hash(doState)
{
  	var phone = $("#phoneRssWrap #phone").attr('value');
	var phone_hash = $("#phoneRssWrap #phone_hash").attr('value');
	  $.ajax({
		   url:"phone.php?act=add_phone",
		   type:"POST",
		   data:'phone=' + phone + '&phone_hash=' + phone_hash + '&doi=' + doState,
		   cache: false,
		   dataType: 'json',
		   success:phone_Response
		   });
	
	return false;
}
function phone_Response(result)
{
   if (result.error == 1)
   {
	   make_smsResponse(result.msg);
   }
   else
   {
  	 if (result.doi == 'add'){
		$donext = 'add_check'
	 }
	 else
	 {
		$donext = 'del_check';
	 }
	 $phone = result.phone;
    $check_txt = '<div id="phoneRssWrap" class="win">'+
	             '<form name="" action="phone.php?act=add_phone" method="POST">'+
	             '<table class="dataTab">'+
				 '<tr><td class="label">认证码：</td><td><input type="text" class="txt" name="phone_hash" id="phone_hash" /></td></tr>'+
	             '<tr><td></td><td><input type="button" value="订 阅" onClick="check_hash('+"$donext"+');" /></td></tr>'+
				 '<input type="hidden" name="phone" id="phone" value="'+$phone+'" />' +
	             '</table>'+
	             '</form>'+
	             '<p id="tip" class="tip">提示：<span class="c1">输入您收到的验证码</span></p>'+
	             '</div>';
      $str = '请输入认证码,进行验证!';	
      $.colorbox({transition:"none", width:380, html:$check_txt, title:$str});	
   }
}
/* jquery 开始####################################### */
$(function(){
//用户中心下拉菜单
if($("#menulist").length > 0){
$umenu = $("#menulist");
$menuwidth=955 - $("#nav ul").width();
$menuheight=58 + $("#ml").height()+'px';
$("#menubox").width($menuwidth);
$umenu.hover(
function(){$umenu.animate({height: $menuheight}, 100);},
function(){$umenu.animate({height: '43px'}, 0);}
);
}
//QQ/EMAL订阅
if($(".deal-share-im").length > 0){
$(".deal-share-im").toggle(function(){
    $(this).parent().parent().parent().find(".deal-share-im-b").css("display","block");},
    function(){
    $(this).parent().parent().parent().find(".deal-share-im-b").css("display","none");});
}
//往期团购列表
if($("#deals-list").length > 0){
$("#deals-list li:even").addClass("lileft");
$("#deals-list li:odd").addClass("liright");
}
//首页产品详情页
if($("#xiangqing").length > 0){
$("#xiangqing img").css({width:"460px"});
}
//表单项激活
if($(".dataTab").length > 0){
	$thisFocus = $(".dataTab input[class='txt'],.dataTab textarea[class='area']");
	$thisFocus.focus(function(){
		$(this).addClass("focus");
	});
	$thisFocus.blur(function(){
		$(this).removeClass("focus");
	});
}
//手机订阅
$phoneRss=$(".phonerss");
$phoneRss.click(function(){
	if ($(this).attr("rel")=="addrss"){
		$str = '短信订阅' + city_name + '最新团购信息';
		$dovalue='add';
	}
	else
	{
		$str = '取消短信订阅';
		$dovalue='del';
	}
$phone_txt = '<div id="phoneRssWrap" class="win">'+
	'<form name="phoneForm" id="phoneForm" action="phone.php?act=add_phone" method="POST">'+
	'<table class="dataTab">'+
	'<tr><td class="label">手机号：</td><td><input type="text" value="" class="txt" name="phone" id="phone" size="40" /></td></tr>'+
	'<tr><td class="label">验证码：</td><td><input type="text" class="txt lf" name="captcha" id="captcha"/><img class="lf" width="100" height="30" src="captcha.php?{$rand}" alt="captcha" onClick="change_captcha(this)" style="cursor:pointer;" /></td></tr>'+
	'<tr><td></td><td><input type="button" value="发 送" onClick="phoneRss('+"$dovalue"+');" /></td></tr></table>'+
	'</form>'+
	'<p id="tip" class="tip">提示：<span class="c1">请输入手机号及验证码获取订阅认证码</span></p>'+
	'</div>';
$.colorbox({transition:"none", width:380, html:$phone_txt, title:$str});	
}); //订阅结束
//登陆、找回密码、修改密码 验证
if($("#loginForm").length > 0){
$("#loginForm").validate({
rules:{
user_name:{required:true},
email:{required:true,email:true},
new_password:{rangelength:[4,16]},
confirm_password:{rangelength:[4,16],equalTo:"#new_password"},
username:{required:true},
password:{required:true}
},
messages:{
user_name:{required:"请输入注册时的用户名"},
email:{required:"请输入注册时的email地址",email:"Email地址格式错误"},
new_password:{rangelength:jQuery.format("密码不能小于{0}个字符")},
confirm_password:{rangelength:jQuery.format("密码不能小于{0}个字符"),equalTo:"两次输入密码不一致"},
username:{required:"请填写用户名或Email"},
password:{required:"请填写密码"}
},
errorPlacement:function(error,element){
    element.next().html(error);
},
success:function(label) {
    label.html("格式正确！").addClass("right");
}
});
}
//购物车购买数量控制JS
if($("#Numinput").length > 0){
	$("#Numinput a").click(function (){
		$thisInput = '#group_num';
		$addNum = Number($($thisInput).attr("value"));
		if($(this).attr("rel") == "add"){
			$($thisInput).attr("value", $addNum + 1);
		}else{
			if($($thisInput).attr("value") > 1){
				$($thisInput).attr("value", $addNum - 1);
				}
		}
	});
}
//购物车验证
if($("#group_address").length > 0){
function check_attr()
{
   if (attr_num > 0)
   {		
      group_attr = get_attr(attr_num);
	  if (!group_attr)
      {
		$.colorbox({transition:"none", width:380, html:'请选择商品属性', title:'信息提示'});
      }
	  return group_attr;
   }
   else
   {
	  return true;
   }
}
jQuery.validator.addMethod("check_area", function(){
  var countryStr = $("#selCountries").find('option:selected').text();	
  var provinceStr = $("#selProvinces").find('option:selected').text();
  var cityStr = $("#selCities").find('option:selected').text();
  var districtStr = $("#selDistricts").find('option:selected').text();
  var district = $('#selDistricts').val();
  var areaStr=countryStr;
  //var areaStr=provinceStr;
  if (provinceStr != countryStr)
  {
    areaStr += provinceStr;
  }
  if (provinceStr != cityStr)
  {
    areaStr += cityStr;
  }
  areaStr += districtStr;
  var address = $('#address').attr('value');
  if (areaStr == address.substring(0,areaStr.length) && district > 0 && address.length > areaStr.length)
  {
   return true;
  }
  else
  {
  	return false;
  }
}, "error");
$("#group_address").validate({
rules:{
consignee:{required:true},
mobile:{required:true},
address:{check_area:true}
},
messages:{
	consignee:{required:"收件人姓名必须填写，以便收件！"},
	mobile:{required:"联系电话必须填写，以便快递联系您和接收快递单号！"},
	address:{check_area:"请选择地区，并补全详细收货地址！"}
},
errorPlacement:function(error,element) {
    element.next().html(error);
},
success:function(label) {
    label.html("格式正确！").addClass("right");
},
submitHandler: function(form){
 if (check_attr())
 {
   form.submit();
 }
}    
});
}
//购物车手机号验证
if($("#group_phone").length > 0){
$("#group_phone").validate({
rules:{
mobile:{required:true,rangelength:[11,11]}
},
messages:{
mobile:{required:"手机号码用来接收优惠券，必须填写！",rangelength:"输入正确的11位手机号码！"}
},
errorPlacement:function(error,element){
    element.next().html(error);
},	
success:function(label) {
    label.html("格式正确！").addClass("right");
}
});
}
//注册验证
if($("#registerForm").length > 0){
jQuery.validator.addMethod("byteRangeLength", function(value, element, param) {    
var length = value.length;    
for(var i = 0; i < value.length; i++){    
   if(value.charCodeAt(i) > 127){    
    length++;    
   }    
}    
return this.optional(element) || ( length >= param[0] && length <= param[1] && /^[\u0391-\uFFE5\w]+$/.test(value));   
}, "error");
if($("#password").length > 0){
	$pwdRegId = "#password";
}
if($("#password_reg").length > 0){
	$pwdRegId = "#password_reg";
}
$("#registerForm").validate({
rules:{
useremail:{required:true,email:true,remote:{url:'signup.php?act=check_email',type:'post'}},
username:{required:true,byteRangeLength:[4,16],remote:{url:'signup.php?act=check_user',type:'post'}},
password_reg:{required:true,rangelength:[4,16]},
password:{required:true,rangelength:[4,16]},
confirm_password:{required:true,equalTo:$pwdRegId},
mobile_phone:{required:true}
},
messages:{
useremail:{required:"Email地址必须填写",email:"Email地址格式错误",remote:"Email地址已经存在"},
username:{required:"请填写用户名",byteRangeLength:jQuery.format("用户名不能少于{0}个字符，一个中文为2字符"),remote:"用户名已存在"},
password:{required:"请填写密码",rangelength:jQuery.format("密码不能小于{0}个字符")},
password_reg:{required:"请填写密码",rangelength:jQuery.format("密码不能小于{0}个字符")},
confirm_password:{required:"请重复填写密码",equalTo:"两次输入密码不一致"},
mobile_phone:{required:"电话号码必须填写"}
},
errorPlacement:function(error,element){
    element.next().html(error);
},	
success:function(label) {
    label.html("可以注册！").addClass("right");
}
});
}
//商家登陆验证
if($("#merchnatForm").length > 0){
$("#merchnatForm").validate({
rules:{
username:{required:true},
password:{required:true}
},
messages:{
username:{required:"请填写商户名"},
password:{required:"请填写密码"}
},
errorPlacement:function(error,element){
    element.next().html(error);
},
success:function(label) {
    label.html("格式正确！").addClass("right");
}
});
}
if($("#merchantEdit").length > 0){
$("#merchantEdit").validate({
rules:{
password:{rangelength:[4,16]},
config_password:{rangelength:[4,16],equalTo:"#password"}
},
messages:{
password:{rangelength:jQuery.format("密码不能小于{0}个字符")},
config_password:{rangelength:jQuery.format("密码不能小于{0}个字符"),equalTo:"两次输入密码不一致"}
},
errorPlacement:function(error,element){
    element.next().html(error);
},
success:function(label) {
    label.html("格式正确！").addClass("right");
}
});
}
//商家后台券验证
if($("#cardForm").length > 0){
    $("#cardForm").validate({
	rules:{
	card_sn:{required:true},
	card_pass:{required:true}
	},
	messages:{
	card_sn:{required:"券号必须填写"},
	card_pass:{required:"密码必须填写"}
	},
	errorPlacement:function(error){
    make_smsResponse(error);
	},
	submitHandler: function(form){
	  set_card();	
    }    
	//success:function(label) {
    //label.html("格式正确").addClass("right");
	//}
	});
}
//讨论区发表话题验证、提交团购信息
if($("#formMsg").length > 0){
	//计算字数
$('#seller_content').bind('focus keyup input paste',function(){
	$maxNum = 110;
	$maxTxt = $(this).attr("value");
	if($maxTxt.length > $maxNum){
	$(this).html($maxTxt.substring(0,$maxNum));
	}else{
	$('#num').text($maxNum - $maxTxt.length);
	}
});
	//表单验证
$("#formMsg").validate({rules:{
forumtitle:{required:true},forumcontent:{required:true},msg_content:{required:true},seller_name:{required:true},seller_phone:{required:true},
city_id:{required:true},seller_content:{required:true}
},
errorPlacement:function(error,element){
    element.next("span").html(error);
}
});
}
//jquery############################################################################
});