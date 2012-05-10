<?php

/**
 * ECGROUPON导入分店程序
 * ============================================================================
 * 网站地址: http://www.ecgroupon.com；
 * ----------------------------------------------------------------------------
 */

define('IN_ECS', true);

/* 包含文件 */
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_code.php');

if ($_REQUEST['act'] == 'action')
{
    /* 检查权限 */
    admin_priv('importsupp');

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
elseif ($_REQUEST['act'] == 'import_supp')
{
    /* 检查权限 */
    admin_priv('importsupp');

    $smarty->assign('ur_here',          $_LANG['batch_supp_add']);
	$smarty->assign('action_link',      array('text'=>$_LANG['batch_supp_add'], 'href'=>'suppliers.php?act=list'));
    $smarty->assign('group_id',           $_REQUEST['group_id']);
    $smarty->display('import_suppliers.htm');
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
    $i = 1;
    $separator = trim($_POST['separator']);
    foreach ($data as $line)
    {
        $row = explode($separator, $line);
        switch(count($row))
        {
			case '3':
                $rec[$i]['suppaddress'] = $row[2];
            case '2':
                $rec[$i]['suppphone'] = $row[1];
            case '1':
                $rec[$i]['suppname']  = $row[0];
                break;
            default:
                $rec[$i]['suppname']  = $row[0];
                $rec[$i]['suppphone'] = $row[1];
				$rec[$i]['suppaddress'] = $row[2];
                break;
        }
		$rec[$i]['suppname']  = ecs_iconv('GB2312','UTF8',$rec[$i]['suppname']); 
	    $rec[$i]['suppaddress']  = ecs_iconv('GB2312','UTF8',$rec[$i]['suppaddress']);
        $i++;
    }
    $sql = "SELECT suppliers_id, suppliers_name, is_check FROM " . $GLOBALS['ecs']->table("suppliers") . " WHERE  parent_id=0 ";
    $parent_suppliers= $GLOBALS['db']->getAll($sql);

    $smarty->assign('ur_here',          $_LANG['batch_supp_add']);
    $smarty->assign('action_link',      array('text'=>$_LANG['batch_supp_add'], 'href'=>'import_suppliers.php?act=import_supp'));
    $smarty->assign('list',               $rec);
	$smarty->assign('parent_suppliers',  $parent_suppliers);
    $smarty->display('suppliers_confirm.htm');

}
/* 批量上传处理 */
elseif ($_REQUEST['act'] == 'batch_insert')
{
    /* 检查权限 */
    admin_priv('importsupp');

    $i = 0;
	$parent_id = intval($_POST['parent_id']);
	if ($parent_id > 0)
    {
	  $rec = array('parent_id' => $parent_id);
      foreach ($_POST['checked'] as $key)
      {
        $rec['suppliers_name']  = trim($_POST['suppname'][$key]);
	    $rec['address']  = trim($_POST['suppaddress'][$key]);
        $rec['phone'] = trim($_POST['suppphone'][$key]);
        $db->AutoExecute($ecs->table('suppliers'), $rec, 'INSERT');
        $i++;
      }
	}
    $link[] = array('text' => $_LANG['card'] , 'href' => 'suppliers.php?act=list');
    sys_msg(sprintf($_LANG['batch_supp_add_ok'], $i) , 0, $link);
}

?>