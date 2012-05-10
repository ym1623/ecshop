<?php

/**
 * ECSHOP 团购卷管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: virtual_card.php 17063 2010-03-25 06:35:46Z liuhui $
*/
$_LANG['lab_goods_name'] = '商品名称';
$_LANG['lab_card_id'] = '编号';
$_LANG['card'] = '团购卷列表';

$_LANG['lab_card_sn'] = '团购卷序号';
$_LANG['lab_card_password'] = '团购卷密码';

$_LANG['import_card_list'] = '团购卷列表';
$_LANG['uploadfile_fail'] = '文件上传失败';
$_LANG['batch_card_add'] = '批量添加补货';
$_LANG['separator'] = '分隔符';
$_LANG['uploadfile'] = '上传文件';
$_LANG['sql_error'] = '第 %s 条信息出错：<br /> ';

/* 提示信息 */
$_LANG['replenish_no_goods_id'] = '缺少商品ID参数，无法进行补货操作';
$_LANG['replenish_no_get_goods_name'] = '商品ID参数有误，无法获取商品名';
$_LANG['drop_card_success'] = '该记录已成功删除';
$_LANG['batch_drop'] = '批量删除';
$_LANG['drop_card_confirm'] = '你确定要删除该记录吗？';
$_LANG['card_sn_exist'] = '团购卷序号 %s 已经存在，请重新输入';
$_LANG['go_list'] = '返回补货列表';
$_LANG['continue_add'] = '继续补货';
$_LANG['uploadfile_fail'] = '文件上传失败';
$_LANG['batch_card_add_ok'] = '已成功添加了 %s 条补货信息';

$_LANG['js_languages']['no_card_sn'] = '团购卷序号和团购卷密码不能都为空。';
$_LANG['js_languages']['separator_not_null'] = '分隔符号不能为空。';
$_LANG['js_languages']['uploadfile_not_null'] = '请选择要上传的文件。';

$_LANG['use_help'] = '使用说明：' .
        '<ol>' .
          '<li>上传文件应为CSV文件<br />' .
              'CSV文件第一列为团购卷序号；第二列为团购卷密码；<br />'.
              '(用EXCEL创建csv文件方法：在EXCEL中按团购卷号、团购卷密码的顺序填写数据，完成后直接保存为csv文件即可)'.
          '<li>团购卷密码可以为空</li>'.
          '<li>团购卷号、团购卷密码中不要使用中文</li>' .
        '</ol>';

?>