<?php

/**
 * ECGROUPON 安装程序
 
 * 网站地址: http://www.ecgroupon.com；
 */

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
    include(ROOT_PATH . 'data/config.php');
    include_once(ROOT_PATH . 'includes/cls_mysql.php');
    include_once(ROOT_PATH . 'includes/cls_sql_executor.php');

  if ($_POST['make'] == 'update')
   {
     $sql_files  = array();
     if (!file_exists(ROOT_PATH . 'sql/updatasql.sql'))
     {
       echo 'sql文件夹中没有updatasql.sql文件';
	   die();
     }
	 else
	 {
	   $sql_files[] = ROOT_PATH.'sql/updatasql.sql';
	 }
      $se = new sql_executor($db,'utf8', 'ecs_', $prefix);
      $result = $se->run_all($sql_files);
      if ($result === false)
      {
        $err->add($se->error);
        echo implode(',', $err->get_all());
      }
      else
	  {
	    clear_all_files();
		$string = '<table cellspacing="0" cellpadding="0" style="margin-top: 100px" align="center">'.
		          '<tr><td>升级成功</td></tr>'.
		          '<tr><td><a href="./team.php">ECGROUPON团购首页</td></tr>'.
				  '<tr><td><a href="./mygroupon">ECGROUPON团购后台管理</td></tr></table>';
		echo $string;
		die();
	  }
	}
	else
	{
		$string = '<table cellspacing="0" cellpadding="0" style="margin-top: 100px" align="center">'.
		          '<tr><td>欢迎使用ECgroupon3.0升级程序,如果您正在使用ECgroupon2.0版本,可以用此升级</td></tr><tr><td>&nbsp;</td></tr>'.
		          '<form method="post">'.
		          '<tr><td><input name="make" value="update" type="hidden"/>'.
				  '<input name="install" value="升级ECgroupon" type="submit" /></td></tr></form></table>';
		echo $string;
	}  


function copy_files($source, $target)
{
    global $err, $_LANG;

    if (!file_exists($target))
    {
        if (!mkdir($target, 0777))
        {
            $err->add($_LANG['cannt_mk_dir']);
            return false;
        }
        @chmod($target, 0777);
    }

    $dir = opendir($source);
    while (($file = @readdir($dir)) !== false)
    {
        if (is_file($source . $file))
        {
            if (!copy($source . $file, $target . $file))
            {
                $err->add($_LANG['cannt_copy_file']);
                return false;
            }
            @chmod($target . $file, 0777);
        }
    }
    closedir($dir);

    return true;
}


?>