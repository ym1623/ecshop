<?php

/**
 * ECGROUPON 批量导入发货单管理
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
*/
$_LANG['lab_goods_name'] = '商品名称';
$_LANG['lab_order_id'] = '编号';
$_LANG['order'] = '发货单列表';

$_LANG['lab_order_sn'] = '订单号';
$_LANG['lab_order_password'] = '发货单号';

$_LANG['import_order_list'] = '订单列表';
$_LANG['uploadfile_fail'] = '文件上传失败';
$_LANG['batch_order_add'] = '批量导入发货单';
$_LANG['separator'] = '分隔符';
$_LANG['uploadfile'] = '上传文件';
$_LANG['sql_error'] = '第 %s 条信息出错：<br /> ';

/* 提示信息 */
$_LANG['replenish_no_goods_id'] = '缺少商品ID参数，无法进行补货操作';
$_LANG['replenish_no_get_goods_name'] = '商品ID参数有误，无法获取商品名';
$_LANG['drop_order_success'] = '该记录已成功删除';
$_LANG['batch_drop'] = '批量删除';
$_LANG['drop_order_confirm'] = '你确定要删除该记录吗？';
$_LANG['order_sn_exist'] = '团购卷序号 %s 已经存在，请重新输入';
$_LANG['go_list'] = '返回发货单列表';
$_LANG['continue_add'] = '继续导入发货单';
$_LANG['uploadfile_fail'] = '文件上传失败';
$_LANG['batch_order_add_ok'] = '已成功导入 %s 条发货单信息';

$_LANG['js_languages']['no_order_sn'] = '订单号和发货单号都不能为空。';
$_LANG['js_languages']['separator_not_null'] = '分隔符号不能为空。';
$_LANG['js_languages']['uploadfile_not_null'] = '请选择要上传的文件。';

$_LANG['use_help'] = '使用说明：' .
        '<ol>' .
		  '<li>使用此功能可以批量设置订单为发货状态,并导入发货单号</li>'.
          '<li>上传文件应为CSV文件<br />' .
              'CSV文件第一列为订单号；第二列为发货单号；<br />'.
              '(用EXCEL创建csv文件方法：在EXCEL中按订单号、发货单号的顺序填写数据，完成后直接保存为csv文件即可)'.
          '<li>订单号不能为空</li>'.
        '</ol>';

?>