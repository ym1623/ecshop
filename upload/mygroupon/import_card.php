<?php

/**
 * ECSHOP 虚拟卡商品管理程序
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

define('IN_ECS', true);

/* 包含文件 */
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_code.php');

/*------------------------------------------------------ */
//-- 补货处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'append_card')
{
    assign_query_info();

    /* 检查权限 */
    admin_priv('importcard');
	$group_id = intval($_REQUEST['group_id']);
    if (empty($group_id))
    {
        $link[] = array('text'=>$_LANG['go_back'],'href'=>'group_buy.php?act=list');
        sys_msg($_LANG['replenish_no_goods_id'], 1, $link);
    }
    else
    {
        $goods_name = $db->GetOne("SELECT goods_name From ".$ecs->table('group_activity')." WHERE group_id='$group_id'");
        if (empty($goods_name))
        {
            $link[] = array('text'=>$_LANG['go_back'],'href'=>'group_buy.php?act=list');
            sys_msg($_LANG['replenish_no_get_goods_name'],1, $link);
        }
    }

    $card = array('group_id'=>$_REQUEST['group_id'],'goods_name'=>$goods_name);
    $smarty->assign('card', $card);

    $smarty->assign('ur_here', $_LANG['replenish']);
    $smarty->assign('action_link', array('text'=>$_LANG['go_list'], 'href'=>'group_card.php?act=list'));
    $smarty->display('replenish_info.htm');
}
elseif ($_REQUEST['act'] == 'action')
{
    /* 检查权限 */
    admin_priv('virualcard');

    $_POST['card_sn'] = trim($_POST['card_sn']);

    /* 加密后的 */
    $coded_card_sn       = encrypt($_POST['card_sn']);
    $coded_old_card_sn   = encrypt($_POST['old_card_sn']);
    $coded_card_password = encrypt($_POST['card_password']);

    /* 在前后两次card_sn不一致时，检查是否有重复记录,一致时直接更新数据 */
    if ($_POST['card_sn'] != $_POST['old_card_sn'])
    {
        $sql = "SELECT count(*) FROM ".$ecs->table('group_card')." WHERE group_id='".$_POST['group_id']."' AND card_sn='$coded_card_sn'";

        if ($db->GetOne($sql) > 0)
        {
             $link[] = array('text'=>$_LANG['go_back'], 'href'=>'group_buy.php?act=list&group_id='.$_POST['group_id']);
             sys_msg(sprintf($_LANG['card_sn_exist'],$_POST['card_sn']),1,$link);
        }
    }

    /* 如果old_card_sn不存在则新加一条记录 */
    if (empty($_POST['old_card_sn']))
    {
        /* 插入一条新记录 */
        $add_date = gmtime();
        $sql = "INSERT INTO ".$ecs->table('group_card')." (group_id, card_sn, card_password,add_date) ".
               "VALUES ('$_POST[group_id]', '$coded_card_sn', '$coded_card_password', '$add_date')";
        $db->query($sql);

        $link[] = array('text'=>$_LANG['go_list'], 'href'=>'virtual_card.php?act=card&goods_id='.$_POST['goods_id']);
        $link[] = array('text'=>$_LANG['continue_add'], 'href'=>'virtual_card.php?act=replenish&goods_id='.$_POST['goods_id']);
        sys_msg($_LANG['action_success'], 0, $link);
    }
    else
    {
        /* 更新数据 */
        $sql = "UPDATE ".$ecs->table('group_card')." SET card_sn='$coded_card_sn', card_password='$coded_card_password'".
               "WHERE card_id='$_POST[card_id]'";
        $db->query($sql);

        $link[] = array('text'=>$_LANG['go_list'], 'href'=>'virtual_card.php?act=card&goods_id='.$_POST['goods_id']);
        $link[] = array('text'=>$_LANG['continue_add'], 'href'=>'virtual_card.php?act=replenish&goods_id='.$_POST['goods_id']);
        sys_msg($_LANG['action_success'], 0, $link);
    }

}

/* 批量上传页面 */
elseif ($_REQUEST['act'] == 'import_card')
{
    /* 检查权限 */
    admin_priv('importcard');

    $smarty->assign('ur_here',          $_LANG['batch_card_add']);
	$smarty->assign('action_link',      array('text'=>$_LANG['import_card_list'], 'href'=>'group_card.php?act=list&group_id=' .  $_REQUEST['group_id']));
    $smarty->assign('group_id',           $_REQUEST['group_id']);
    $smarty->display('import_card.htm');
}

elseif ($_REQUEST['act'] == 'batch_confirm')
{
    /* 检查上传是否成功 */
    if ($_FILES['uploadfile']['tmp_name'] == '' || $_FILES['uploadfile']['tmp_name'] == 'none')
    {
        sys_msg($_LANG['uploadfile_fail'], 1);
    }

    $data = file($_FILES['uploadfile']['tmp_name']);
    $rec = array(); //数据数组
    $i = 0;
    $separator = trim($_POST['separator']);
    foreach ($data as $line)
    {
        $row = explode($separator, $line);
        switch(count($row))
        {
            case '2':
                $rec[$i]['card_password'] = $row[1];
            case '1':
                $rec[$i]['card_sn']  = $row[0];
                break;
            default:
                $rec[$i]['card_sn']  = $row[0];
                $rec[$i]['card_password'] = $row[1];
                break;
        }
        $i++;
    }

    $smarty->assign('ur_here',          $_LANG['batch_card_add']);
    $smarty->assign('action_link',      array('text'=>$_LANG['batch_card_add'], 'href'=>'import_card.php?act=import_card&group_id='.$_REQUEST['group_id']));
    $smarty->assign('list',               $rec);
    $smarty->display('group_card_confirm.htm');

}
/* 批量上传处理 */
elseif ($_REQUEST['act'] == 'batch_insert')
{
    /* 检查权限 */
    admin_priv('importcard');

    $add_time = gmtime();
    $i = 0;
    foreach ($_POST['checked'] as $key)
    {
        $rec['card_sn']  = trim($_POST['card_sn'][$key]);
        $rec['card_password'] = trim($_POST['card_password'][$key]);
        $rec['group_id'] = $_POST['group_id'];
        $rec['add_date'] = $add_time;
        $db->AutoExecute($ecs->table('group_card'), $rec, 'INSERT');
        $i++;
    }

    $link[] = array('text' => $_LANG['card'] , 'href' => 'group_card.php?act=list&group_id='.$_POST['group_id']);
    sys_msg(sprintf($_LANG['batch_card_add_ok'], $i) , 0, $link);
}

?>