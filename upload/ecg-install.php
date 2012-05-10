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

  if ($_POST['make'] == 'install')
   {
     $sql_files  = array();
     if (!file_exists(ROOT_PATH . 'sql/groupsql.sql'))
     {
       echo 'sql文件夹中没有groupsql.sql文件';
	   die();
     }
	 else
	 {
	   $sql_files[] = ROOT_PATH.'sql/groupsql.sql';
	 }
	 if ($_POST['install_data'] == 1)
	 {
       if (!file_exists(ROOT_PATH . 'sql/groupdata.sql'))
       {
         echo 'sql文件夹中没有groupdata.sql文件';
	     die();
       }
	   $sql_files[] = ROOT_PATH.'sql/groupdata.sql';
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
	    if ($_POST['install_data'] == 1)
		{
		  copy_files(ROOT_PATH . 'sql/201009/', ROOT_PATH . 'images/201009/');
		}
	    clear_all_files();
		$string = '<table cellspacing="0" cellpadding="0" style="margin-top: 100px" align="center">'.
		          '<tr><td>安装成功</td></tr>'.
		          '<tr><td><a href="./team.php">ECGROUPON团购首页</td></tr>'.
				  '<tr><td><a href="./mygroupon">ECGROUPON团购后台管理</td></tr></table>';
		echo $string;
		die();
	  }
	}
	else
	{
		$string = '<table cellspacing="0" cellpadding="0" style="margin-top: 100px" align="center">'.
		          '<tr><td>欢迎使用ECGROUPON团购开源程序</td></tr><tr><td>&nbsp;</td></tr>'.
		          '<form method="post"><tr><td><input name="install_data" value="1" type="checkbox"/>安装测试数据</td></tr>'.
		          '<tr><td><input name="make" value="install" type="hidden"/>'.
				  '<input name="install" value="安装ECgroupon" type="submit" /></td></tr></form></table>';
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