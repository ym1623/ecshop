ALTER TABLE `ecs_group_city` ADD `eng_name` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `ecs_group_activity` ADD `sort_order` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
 ADD `is_show` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1';


ALTER TABLE `ecs_suppliers` ADD `is_ship` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
 ADD `suppliers_site` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
 ADD `suppliers_area` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
 
ALTER TABLE `ecs_users` ADD `aliss_name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `ecs_group_card` ADD `suppliers_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `ecs_group_seller` ADD `seller_type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1';

DROP TABLE IF EXISTS `ecs_expand_suppliers`;
CREATE TABLE `ecs_expand_suppliers` (
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `suppliers_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`suppliers_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `ecs_login_config`;
CREATE TABLE `ecs_login_config` (
  `config_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `web_name` varchar(50) DEFAULT NULL,
  `web_url` varchar(150) DEFAULT NULL,
  `app_key` varchar(150) DEFAULT NULL,
  `app_secret` varchar(150) DEFAULT NULL,
  `web_from` varchar(150) DEFAULT NULL,
  `app_encrypt` varchar(150) DEFAULT NULL,
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `web_login` varchar(255) DEFAULT NULL,
  `login_img` varchar(255) CHARACTER SET ucs2 DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `ecs_login_config` (`config_id`, `web_name`, `web_url`, `app_key`, `app_secret`, `web_from`, `app_encrypt`, `is_open`, `web_login`, `login_img`, `sort_order`) VALUES
(2, '2345导航', 'http://tuan.2345.com', '', '', '2345', '33333', 1, 'oauth/2345/2345login.php', 'oauth/2345/imgs/big_login.gif', 0),
(3, '360导航', 'http://hao.360.cn/', '', '', 'hao360', '', 1, 'oauth/hao360/360login.php', 'oauth/hao360/imgs/big_login.gif', 0),
(4, 'tuan800', 'www.tuan800.com', '', '', 'tuan800', '', 1, 'oauth/tuan800/tuanlogin.php', 'oauth/tuan800/big_login.gif', 0),
(5, '购团', 'www.goutuan.net', '', '', 'goutuan', '', 1, 'oauth/goutuan/index.php', 'oauth/goutuan/imgs/big_login.gif', 0),
(6, 'QQ科技', 'http://www.qq.com', '', '', 'qq', '', 1, 'oauth/qq/qqlogin.php', 'oauth/qq/img/qq_login.png', 0);
INSERT INTO `ecs_admin_action` (`action_id`,`parent_id`,`action_code`,`relevance`)VALUES 
('' , '4', 'importsupp', ''),
('', '4', 'importsupp', ''),
('', '6', 'importorder', ''),
('', '5', 'login_config', '');

INSERT INTO `ecs_shop_config` (`id` ,`parent_id` ,`code` ,`type` ,`store_range` ,`store_dir` ,`value` ,`sort_order`)VALUES
('', '20', 'showmode', 'select', '0,1', '', '0', '1'),
('', '20', 'groupindex', 'select', '0,1', '', '0', '1');

INSERT INTO `ecs_friend_link` ( `link_name`, `link_url`) VALUES
('麦圈网','http://www.much001.com'),
('360团','http://tuan.360.cn'),
('好123','http://tuan.hao123.com'),
('团800','http://www.tuan800.com'),
('购团','http://www.goutuan.net'),
('1000团','http://www.1000tuan.com'),
('团p','http://www.tuanp.com'),
('57团','http://www.57tuan.com'),
('我是团长','http://www.54tz.com'),
('找团购','http://tuan.bj100.com'),
('团138','http://www.tuan138.com'),
('搜狐','http://123.sohu.com'),
('soso','http://tuan.soso.com'),
('赶集','http://bj.ganji.com/tuan/'),
('来优','http://www.letyo.com'),
('2345','http://tuan.2345.com'),
('迅购','http://www.xungou.com'),
('最佳团购','http://www.topzj.com'),
('人人折','http://www.renrenzhe.com');

