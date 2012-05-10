/* $Id : common.js 4865 2007-01-31 14:04:10Z paulgao $ */

function update(rid,group_id)
{
     var str = '';
	 var district = $('#selDistricts').val();
	 if (district > 0)
	 {
	   var country = $('#selCountries').val();
	   var province = $('#selProvinces').val();
	   var city = $('#selCities').val();	 
       str = '&country=' + country +'&province=' + province + '&city=' + city + '&district=' + district;
	 }
	 if (attr_num > 0)
	 { 
	   var spec_arr = get_attr(-1);
       if (spec_arr)
	   {
	 	str += '&group_attr=' + spec_arr;
	   }
	 }
	 var num = $('#group_num').val();
	 if (num > 0)
	 {
	  $.ajax({
		   url:"buy.php?a=update",
		   type:'post',
		   data:'number=' + num + '&rid=' + rid + '&group_id=' + group_id + str,
		   cache: false,
		   dataType: 'json',
		   success: updateGroupResponse
		   });
	 }
}
function updateGroupResponse(result)
{  
  if (result.error == 0)
  {
	str = result.limitnum > 0 ? '限购' + result.limitnum + '件' : '';
	$("#limitnum").attr('innerHTML',str);
	$("#free_money").attr('innerHTML',result.free_money);
	$("#subtotal").attr('innerHTML',result.formated_total_cart);
    $("#group_num").attr('value', result.num);
	$("#goods_price").attr('innerHTML',result.formated_group_price);
	$("#total_order").attr('innerHTML',result.formated_total_order);
	$('#shipping_fee').attr('innerHTML',result.formated_shipping_fee);
  }
}
function check_bonus(orderid)
{
	var bonus_sn = $('#bonus_sn').val();
	var orderid = $('#orderid').val();
	if (bonus_sn != '')
	{
	  $.ajax({
		   url:"buy.php?a=check_bonus",
		   type:'post',
		   data:'bonus_sn=' + bonus_sn +'&orderid=' + orderid,
		   cache: false,
		   dataType: 'json',
		   success: checkbonusResponse
		   });
	}
}
function checkbonusResponse(result)
{  
  if (result.error == 0)
  {
	$("#bonus_money").attr('innerHTML',result.formated_bonus_money);
	$("#pay_money").attr('innerHTML',result.content);
  }
}


function checkOrder(frm)
{
  var paymentSelected = false;

  // 检查是否选择了支付配送方式
  for (i = 0; i < frm.elements.length; i ++ )
  {
    if (frm.elements[i].name == 'payment' && frm.elements[i].checked)
    {
      paymentSelected = true;
    }
  }

  if (!paymentSelected)
  {
	$.colorbox({transition:"none", width:420, html:'<div class="tip">请选择支付方式!!!</div>', title:'选择支付方式'});
    //$.prompt('请选择支付方式',{promptspeed:'fast',top:150,prefix:'jqi',show:'slideDown',buttons:{}});
    return false;
  }

  return true;
}
function get_attr(attr_num)
{
  var frmattr=document.forms['frm_attr'];
  var spec_arr = new Array();
  var j = 0;
  for (i = 0; i < frmattr.elements.length; i ++ )
  {
    var prefix = frmattr.elements[i].name.substr(0, 5);

    if (prefix == 'spec_' && (frmattr.elements[i].type == 'radio' || frmattr.elements[i].type == 'checkbox'))
    {
	  if (frmattr.elements[i].type == 'radio')
	  {
	    if (frmattr.elements[i].checked)
		{	  
         spec_arr[j] = frmattr.elements[i].value;
         j++ ;
		}
	  }
	  if (frmattr.elements[i].type == 'checkbox')
	  {
	    if (frmattr.elements[i].checked)
		{	  
         spec_arr[j] = frmattr.elements[i].value;
		}
		j++ ;
	  }
    }
  }
  if (attr_num == -1)
  {
     return spec_arr;
  }
  if (attr_num == j)
  {
    $("#show_attr").attr('innerHTML','');	  
   return spec_arr;
  }
  else
  {  
	return false;  
  } 
}
function submit_vote()
{
  var frm     = document.forms['ECS_VOTEFORM'];
  var type    = frm.elements['type'].value;
  var vote_id = frm.elements['id'].value;
  var option_id = 0;

  if (frm.elements['option_id'].checked)
  {
    option_id = frm.elements['option_id'].value;
  }
  else
  {
    for (i=0; i<frm.elements['option_id'].length; i++ )
    {
      if (frm.elements['option_id'][i].checked)
      {
        option_id = (type == 0) ? option_id + "," + frm.elements['option_id'][i].value : frm.elements['option_id'][i].value;
      }
    }
  }

  if (option_id == 0)
  {
    return;
  }
  else
  {
	$.ajax({
		   url:"group_vote.php",
		   type:'post',
		   data:'vote=' + vote_id + '&options=' + option_id + "&type=" + type,
		   cache: false,
		   dataType: 'json',
		   success: voteResponse
		   });  
  }
}

/**
 * 处理投票的反馈信息
 */
function voteResponse(result)
{
  if (result.message.length > 0)
  {
	$.colorbox({transition:"none", width:420, html:result.message, title:'投票提示'});
  }
  if (result.error == 0)
  {
    var layer = document.getElementById('ECS_VOTE');

    if (layer)
    {
      layer.innerHTML = result.content;
    }
  }
}

function shipping_free()
{
	var country = $('#selCountries').val();
	var province = $('#selProvinces').val();
	var city = $('#selCities').val();
	var district = $('#selDistricts').val();
	if (district > 0 || (country <= 0 && province <= 0 && city <= 0 && district <= 0))
	{
	  $.ajax({
		   url:"buy.php?a=shipping",
		   type:'post',
		   data:'country=' + country +'&province=' + province + '&city=' + city + '&district=' + district,
		   cache: false,
		   dataType: 'json',
		   success: shippingfreeResponse
		   });
	 }
}
function shippingfreeResponse(result)
{  
  if (result.error == 0)
  {
	str =result.free_money > 0 ? '满' + result.formated_free_money + '免运费' : '';
    $("#free_money").attr('innerHTML',str);
	$("#total_order").attr('innerHTML',result.formated_total_order);
	$('#shipping_fee').attr('innerHTML',result.formated_shipping_fee);
	region.set_address();
  }
}

