<?php

/**
 * ECSHOP 邮件列表管理
 * ===========================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liuhui $
 * $Id: phone_list.php 17063 2010-03-25 06:35:46Z liuhui $
 */
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_group.php');
require_once(dirname(__FILE__) . '/languages/sms.php');

admin_priv('phone_list');

if ($_REQUEST['act'] == 'list')
{
    admin_priv('phone_manage');

    $phonedb = get_phone_list();
	$stat_list = $_LANG['stat'];
	unset($stat_list['name']);
    $smarty->assign('full_page',    1);
    $smarty->assign('ur_here', $_LANG['04_phone_manage']);
	$smarty->assign('city_list',       get_group_city());
	$smarty->assign('stat_list',       $stat_list);
    $smarty->assign('phonedb',      $phonedb['phonedb']);
    $smarty->assign('filter',       $phonedb['filter']);
    $smarty->assign('record_count', $phonedb['record_count']);
    $smarty->assign('page_count',   $phonedb['page_count']);
    assign_query_info();
    $smarty->display('phone_list.htm');
}
elseif ($_REQUEST['act'] == 'export')
{
        admin_priv('phone_manage');

	$city_id = isset($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) : 0;
    $sql = "SELECT phone FROM " . $ecs->table('phone_list') . "WHERE stat = 1";
	if ($city_id > 0)
	{
	  $sql .= " AND city_id='$city_id'";
	}
    $phones = $db->getAll($sql);
    $out = '';
    foreach ($phones as $key => $val)
    {
        $out .= "$val[phone]\n";
    }
    $contentType = 'text/plain';
    $len = strlen($out);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s',time()+31536000) .' GMT');
    header('Pragma: no-cache');
    header('Content-Encoding: none');
    header('Content-type: ' . $contentType);
    header('Content-Length: ' . $len);
    header('Content-Disposition: attachment; filename="phone_list.txt"');
    echo $out;
    exit;
}
elseif ($_REQUEST['act'] == 'query')
{
    $phonedb = get_phone_list();
    $smarty->assign('phonedb',      $phonedb['phonedb']);
    $smarty->assign('filter',       $phonedb['filter']);
    $smarty->assign('record_count', $phonedb['record_count']);
    $smarty->assign('page_count',   $phonedb['page_count']);

    $sort_flag  = sort_flag($phonedb['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('phone_list.htm'), '',
        array('filter' => $phonedb['filter'], 'page_count' => $phonedb['page_count']));
}

/*------------------------------------------------------ */
//-- 批量删除
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_remove')
{
       admin_priv('phone_manage');

    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_select_email'], 1);
    }

    $sql = "DELETE FROM " . $ecs->table('phone_list') .
            " WHERE id " . db_create_in(join(',', $_POST['checkboxes']));
    $db->query($sql);

    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'phone_list.php?act=list');
    sys_msg(sprintf($_LANG['batch_remove_succeed'], $db->affected_rows()), 0, $lnk);
}

/*------------------------------------------------------ */
//-- 批量恢复
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_unremove')
{
        admin_priv('phone_manage');

    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_select_email'], 1);
    }

    $sql = "UPDATE " . $ecs->table('phone_list') .
            " SET stat = 1 WHERE stat <> 1 AND id " . db_create_in(join(',', $_POST['checkboxes']));
    $db->query($sql);

    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'phone_list.php?act=list');
    sys_msg(sprintf($_LANG['batch_unremove_succeed'], $db->affected_rows()), 0, $lnk);
}

/*------------------------------------------------------ */
//-- 批量退订
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_exit')
{
       admin_priv('phone_manage');

    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_select_email'], 1);
    }

    $sql = "UPDATE " . $ecs->table('phone_list') .
            " SET stat = 2 WHERE stat <> 2 AND id " . db_create_in(join(',', $_POST['checkboxes']));
    $db->query($sql);

    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'phone_list.php?act=list');
    sys_msg(sprintf($_LANG['batch_exit_succeed'], $db->affected_rows()), 0, $lnk);
}
/*------------------------------------------------------ */
//-- 发送短信
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'send_sms')
{
       admin_priv('phone_manage');

    if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
    {
        sys_msg($_LANG['no_select_email'], 1);
    }
	$msg = trim($_POST['sms_content']);
	if ($msg == '')
	{
	    sys_msg($_LANG['empty_sms_content'], 1);
	}
    require_once(ROOT_PATH . 'includes/cls_sms.php');
    $sms = new sms();
    $send_date = '';   
    $sql = "SELECT phone FROM " . $ecs->table('phone_list') . "WHERE stat = 1 AND id " . db_create_in(join(',', $_POST['checkboxes']));
    $phone_arr = $db->getCol($sql);
	$phone = join(',', $phone_arr);
    $result = $sms->send($phone, $msg, $send_date, $send_num = 13);


    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'phone_list.php?act=list');
	if ($result === true)//发送成功
    {
         sys_msg($_LANG['send_exit_succeed'], 0, $lnk);
    }
    else
    {
         @$error_detail = $_LANG['server_errors'][$sms->errors['server_errors']['error_no']]
                          . $_LANG['api_errors']['send'][$sms->errors['api_errors']['error_no']];
          sys_msg($_LANG['send_error'] . $error_detail, 1, $link);
    }
}

function get_phone_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['sort_by']      = empty($_REQUEST['sort_by']) ? 'stat' : trim($_REQUEST['sort_by']);
        $filter['sort_order']   = empty($_REQUEST['sort_order']) ? 'ASC' : trim($_REQUEST['sort_order']);
        $filter['city_id'] = isset($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) : 0;
        $filter['phone'] = isset($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
		$filter['stat'] = isset($_REQUEST['stat']) ? intval($_REQUEST['stat']) : '-1';
       
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('phone_list') . " WHERE 1";
		if ($filter['city_id'] > 0)
		{
		  $sql .= " AND city_id='$filter[city_id]'";
		}
		if ($filter['phone'] != '')
		{
		  $sql .= " AND phone='$filter[phone]'";
		}
		if ($filter['stat'] >= 0)
		{
		  $sql .= " AND stat='$filter[stat]'";
		}
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        /* 查询 */

        $sql = "SELECT p.*,c.city_name FROM " . $GLOBALS['ecs']->table('phone_list') .
		       " AS p LEFT JOIN " . $GLOBALS['ecs']->table('group_city') . " AS c ON c.city_id=p.city_id WHERE 1";
        if ($filter['city_id'] > 0)
		{
		  $sql .= " AND p.city_id='$filter[city_id]'";
		}
		if ($filter['phone'] != '')
		{
		  $sql .= " AND p.phone='$filter[phone]'";
		}
		if ($filter['stat'] >= 0)
		{
		  $sql .= " AND p.stat='$filter[stat]'";
		}
		$sql .=  " ORDER BY " . $filter['sort_by'] . ' ' . $filter['sort_order'] . " LIMIT " . $filter['start'] . ",$filter[page_size]";
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $phonedb = $GLOBALS['db']->getAll($sql);

    $arr = array('phonedb' => $phonedb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;


}
?>