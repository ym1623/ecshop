<?php

/**
 * ECSHOP 管理中心菜单数组
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: inc_menu.php 17063 2010-03-25 06:35:46Z liuhui $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$modules['02_cat_and_group']['08_category_list']    = 'group_class.php?act=list';
$modules['02_cat_and_group']['08_category_add_group']    = 'group_class.php?act=add';


$modules['02_cat_and_group']['05_comment_manage']   = 'comment_manage.php?act=list';
$modules['02_cat_and_group']['08_goods_type']       = 'goods_type.php?act=manage';
$modules['02_cat_and_group']['09_group_seller']       = 'group_seller.php?act=list';

$modules['02_cat_and_group']['04_groupcard_list']       = 'group_card.php?act=list';

$modules['02_cat_and_group']['04_bonustype_list']       = 'bonus.php?act=list';
$modules['02_cat_and_group']['03_group_buy']            = 'group_buy.php?act=list';
$modules['02_cat_and_group']['03_group_add']            = 'group_buy.php?act=add';
$modules['02_cat_and_group']['09_groupnavs_list']         = 'group_navigation.php?act=list';

$modules['03_city_and_group']['08_group_city_add']   = 'group_city.php?act=add';
$modules['03_city_and_group']['08_group_city_list']  = 'group_city.php?act=list';

$modules['03_suppliers_manage']['09_suppliers_addcat']    = 'group_class.php?act=list&type=2';
$modules['03_suppliers_manage']['08_category_add_group'] = 'group_class.php?act=add&type=2';

$modules['03_suppliers_manage']['suppliers_list']   = 'suppliers.php?act=list'; // 供货商
$modules['03_suppliers_manage']['suppliers_add']   = 'suppliers.php?act=add'; // 供货商
$modules['03_suppliers_manage']['import_suppliers']   = 'import_suppliers.php?act=import_supp'; // 供货商

$modules['04_order']['02_order_list']               = 'order.php?act=list';
$modules['04_order']['03_order_query']              = 'order.php?act=order_query';
$modules['04_order']['04_merge_order']              = 'order.php?act=merge';
$modules['04_order']['05_edit_order_print']         = 'order.php?act=templates';
//$modules['04_order']['07_repay_application']        = 'repay.php?act=list_all';
$modules['04_order']['09_delivery_order']           = 'order.php?act=delivery_list';
$modules['04_order']['10_back_order']               = 'order.php?act=back_list';
$modules['04_order']['11_improt_order']             = 'import_order.php?act=import_order';

$modules['05_banner']['ad_position']                = 'ad_position.php?act=list';
$modules['05_banner']['ad_list']                    = 'ads.php?act=list';

$modules['06_stats']['searchengine_stats']          = 'searchengine_stats.php?act=view';
$modules['06_stats']['report_guest']                = 'guest_stats.php?act=list';
$modules['06_stats']['report_order']                = 'order_stats.php?act=list';
$modules['06_stats']['report_sell']                 = 'sale_general.php?act=list';
$modules['06_stats']['sale_list']                   = 'sale_list.php?act=list';

$modules['07_content']['03_article_list']           = 'article.php?act=list';
$modules['07_content']['02_articlecat_list']        = 'articlecat.php?act=list';
$modules['07_content']['vote_list']                 = 'vote.php?act=list';


$modules['08_members']['03_users_list']             = 'users.php?act=list';
$modules['08_members']['04_users_add']              = 'users.php?act=add';
$modules['08_members']['05_user_rank_list']         = 'user_rank.php?act=list';
$modules['08_members']['06_list_integrate']         = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg']            = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account']           = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage']    = 'user_account_manage.php?act=list';

$modules['10_priv_admin']['admin_logs']             = 'admin_logs.php?act=list';
$modules['10_priv_admin']['admin_list']             = 'privilege.php?act=list';
$modules['10_priv_admin']['agency_list']            = 'agency.php?act=list';

$modules['11_system']['01_shop_config']             = 'shop_config.php?act=list_edit';
$modules['11_system']['02_payment_list']            = 'payment.php?act=list';
$modules['11_system']['03_shipping_list']           = 'shipping.php?act=list';
$modules['11_system']['04_mail_settings']           = 'shop_config.php?act=mail_settings';
$modules['11_system']['05_area_list']               = 'area_manage.php?act=list';
$modules['11_system']['08_friendlink_list']         = 'friend_link.php?act=list';
$modules['11_system']['captcha_manage']             = 'captcha_manage.php?act=main';
$modules['11_system']['ucenter_setup']              = 'integrate.php?act=setup&code=ucenter';
$modules['11_system']['22_group_login']             = 'group_login.php?act=list';

$modules['12_template']['02_template_select']       = 'template.php?act=list';
$modules['12_template']['04_template_library']      = 'template.php?act=library';

$modules['12_template']['mail_template_manage']     = 'mail_template.php?act=list';


$modules['13_backup']['02_db_manage']               = 'database.php?act=backup';
$modules['13_backup']['03_db_optimize']             = 'database.php?act=optimize';
$modules['13_backup']['04_sql_query']               = 'sql.php?act=main';
$modules['13_backup']['convert']                    = 'convert.php?act=main';


$modules['14_sms']['03_sms_send']                   = 'sms.php?act=display_send_ui';
$modules['14_sms']['04_sms_template_manage']           = 'sms_template.php?act=list';
$modules['14_sms']['05_phone_manage']                  = 'phone_list.php?act=list';

$modules['15_rec']['affiliate']                     = 'affiliate.php?act=list';
$modules['15_rec']['affiliate_ck']                  = 'affiliate_ck.php?act=list';

$modules['16_email_manage']['magazine_list']        = 'magazine_list.php?act=list';
$modules['16_email_manage']['email_list']           = 'email_list.php?act=list';
$modules['16_email_manage']['view_sendlist']        = 'view_sendlist.php?act=list';
$modules['17_forum_manage']['01_add_forumcat'] = 'group_class.php?act=add&type=3';
$modules['17_forum_manage']['02_forumcat']          = 'group_class.php?act=list&type=3';
$modules['17_forum_manage']['03_forumlist']         = 'forum_manage.php?act=list';

?>
