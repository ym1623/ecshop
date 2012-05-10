
-- --------------------------------------------------------
--
-- `ecs_group_activity`
--

DROP TABLE IF EXISTS `ecs_group_activity`;
CREATE TABLE `ecs_group_activity` (
  `group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `succeed_time` int(10) unsigned DEFAULT '0',
  `group_name` varchar(255) NOT NULL,
  `group_desc` text NOT NULL,
  `group_type` tinyint(3) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `is_finished` tinyint(3) unsigned NOT NULL,
  `ext_info` text NOT NULL,
  `city_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `upper_orders` int(11) unsigned NOT NULL DEFAULT '0',
  `lower_orders` int(10) unsigned NOT NULL DEFAULT '0',
  `pos_express` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `market_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `group_image` varchar(255) NOT NULL,
  `suppliers_id` smallint(5) unsigned DEFAULT '0',
  `group_keywords` varchar(255) DEFAULT NULL,
  `group_description` varchar(255) DEFAULT NULL,
  `goods_comment` varchar(255) DEFAULT NULL,
  `group_comment` varchar(255) DEFAULT NULL,
  `group_brief` varchar(255) DEFAULT NULL,
  `goods_type` tinyint(1) unsigned DEFAULT NULL,
  `goods_rebate` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `group_attr` smallint(5) unsigned DEFAULT '0',
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `group_stock` int(11) unsigned DEFAULT NULL,
  `closed_time` int(11) unsigned NOT NULL DEFAULT '0',
  `group_restricted` smallint(5) unsigned DEFAULT '0',
  `small_desc` varchar(255) DEFAULT NULL,
  `past_time` int(10) unsigned NOT NULL DEFAULT '0',
  `goods_name` varchar(255) NOT NULL,
  `group_need` tinyint(1) unsigned DEFAULT '1',
  `group_freight` smallint(5) unsigned NOT NULL DEFAULT '0',
  `already_orders` smallint(5) unsigned NOT NULL DEFAULT '0',
  `activity_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `goods_weight` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `shipping_id` smallint(3) unsigned NOT NULL DEFAULT '0',
  `is_hdfk` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_limit` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`group_id`),
  KEY `city_id` (`city_id`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- `ecs_group_city`
--

DROP TABLE IF EXISTS `ecs_group_city`;
CREATE TABLE `ecs_group_city` (
  `city_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `city_name` varchar(30) NOT NULL,
  `city_notice` varchar(255) NOT NULL,
  `city_desc` varchar(255) NOT NULL,
  `is_open` tinyint(1) unsigned DEFAULT '0',
  `city_title` varchar(255) NOT NULL,
  `city_keyword` varchar(255) NOT NULL,
  `city_qq` varchar(255) NOT NULL,
  `city_sort` smallint(5) unsigned NOT NULL DEFAULT '0',
  `eng_name` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


-- --------------------------------------------------------

--
-- `ecs_group_gallery`
--

DROP TABLE IF EXISTS `ecs_group_gallery`;
CREATE TABLE `ecs_group_gallery` (
  `img_id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `img_desc` varchar(255) NOT NULL default '',
  `img_url` varchar(255) NOT NULL,
  PRIMARY KEY  (`img_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- `ecs_group_card`
--

DROP TABLE IF EXISTS `ecs_group_card`;
CREATE TABLE `ecs_group_card` (
  `card_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `card_sn` varchar(60) NOT NULL DEFAULT '',
  `card_password` varchar(60) NOT NULL DEFAULT '',
  `add_date` int(11) NOT NULL DEFAULT '0',
  `end_date` int(11) unsigned NOT NULL DEFAULT '0',
  `is_saled` tinyint(1) NOT NULL DEFAULT '0',
  `order_sn` varchar(20) NOT NULL DEFAULT '',
  `is_used` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `use_date` int(11) unsigned NOT NULL DEFAULT '0',
  `send_num` smallint(5) unsigned DEFAULT '0',
  `suppliers_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`card_id`),
  KEY `group_id` (`group_id`),
  KEY `car_sn` (`card_sn`),
  KEY `order_sn` (`order_sn`),
  KEY `is_saled` (`is_saled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


-- --------------------------------------------------------

--
--`ecs_group_attr`
--

DROP TABLE IF EXISTS `ecs_group_attr`;
CREATE TABLE `ecs_group_attr` (
  `group_attr_id` int(10) unsigned NOT NULL auto_increment,
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `attr_id` smallint(5) unsigned NOT NULL default '0',
  `attr_value` text NOT NULL,
  `attr_price` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`group_attr_id`),
  KEY `group_id` (`group_id`),
  KEY `attr_id` (`attr_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- `ecs_friend_comment`
--
DROP TABLE IF EXISTS `ecs_friend_comment`;
CREATE TABLE `ecs_friend_comment` (
  `fid` mediumint(8) unsigned NOT NULL auto_increment,
  `friend_name` varchar(30) NOT NULL,
  `friend_url` varchar(50) NOT NULL,
  `friend_web` varchar(30) NOT NULL,
  `friend_desc` varchar(255) NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`fid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- `ecs_expand_city`
--
DROP TABLE IF EXISTS `ecs_expand_city`;
CREATE TABLE `ecs_expand_city` (
  `group_id` mediumint(8) unsigned NOT NULL default '0',
  `city_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- `ecs_group_forum`
--
DROP TABLE IF EXISTS `ecs_group_forum`;
CREATE TABLE `ecs_group_forum` (
  `forum_id` int(10) unsigned NOT NULL auto_increment,
  `user_name` varchar(60) NOT NULL default '',
  `forum_content` text NOT NULL,
  `forum_sort` int(10) unsigned NOT NULL default '0',
  `add_time` int(10) unsigned NOT NULL default '0',
  `ip_address` varchar(15) NOT NULL default '',
  `forum_status` tinyint(3) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `cid` smallint(5) unsigned default '0',
  `city_id` smallint(5) unsigned default '0',
  `forum_title` varchar(255) NOT NULL,
  `click_num` int(11) unsigned NOT NULL default '0',
  `forum_type` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`forum_id`),
  KEY `parent_id` (`parent_id`),
  KEY `cid` (`cid`),
  KEY `city_id` (`city_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
--  `ecs_group_navigation`
--
DROP TABLE IF EXISTS `ecs_group_navigation`;
CREATE TABLE `ecs_group_navigation` (
  `nav_id` smallint(5) unsigned NOT NULL auto_increment,
  `nav_name` varchar(255) NOT NULL default '',
  `nav_url` varchar(255) NOT NULL default '',
  `show_order` tinyint(3) unsigned NOT NULL default '0',
  `is_show` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`nav_id`),
  KEY `show_order` (`show_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `ecs_group_navigation` (`nav_id`, `nav_name`, `nav_url`, `show_order`, `is_show`) VALUES
(2, '往期团购', 'stage.php', 6, 1),
(3, '首页', 'team.php', 7, 1),
(4, '秒杀抢团', 'seconds.php', 5, 1),
(5, '热销商品', 'hots.php', 4, 1),
(6, '品牌商户', 'partner.php', 3, 1),
(7, '团购达人', 'topman.php', 2, 1),
(8, '邮件订阅', 'subscribe.php', 1, 1),
(9, '讨论区', 'forum.php', 0, 1);

--
--  `ecs_group_seller`
--
DROP TABLE IF EXISTS `ecs_group_seller`;
CREATE TABLE `ecs_group_seller` (
  `seller_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `seller_name` varchar(60) NOT NULL,
  `seller_content` text NOT NULL,
  `seller_time` int(10) unsigned NOT NULL DEFAULT '0',
  `seller_phone` varchar(255) NOT NULL DEFAULT '0',
  `other_phone` varchar(255) NOT NULL DEFAULT '0',
  `city_id` int(11) unsigned NOT NULL DEFAULT '0',
  `from_ip` varchar(15) NOT NULL,
  `seller_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`seller_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


--
--  `ecs_phone_list`
--
DROP TABLE IF EXISTS `ecs_phone_list`;
CREATE TABLE `ecs_phone_list` (
  `id` mediumint(8) NOT NULL auto_increment,
  `city_id` smallint(5) unsigned NOT NULL default '0',
  `phone` varchar(60) NOT NULL,
  `stat` tinyint(1) NOT NULL default '0',
  `hash` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- `ecs_group_class`
--
DROP TABLE IF EXISTS `ecs_group_class`;
CREATE TABLE `ecs_group_class` (
  `cid` smallint(5) unsigned NOT NULL auto_increment,
  `class_name` varchar(90) NOT NULL default '',
  `keywords` varchar(255) NOT NULL default '',
  `class_desc` varchar(255) NOT NULL default '',
  `sort_order` tinyint(1) unsigned NOT NULL default '0',
  `is_show` tinyint(1) unsigned NOT NULL default '1',
  `class_type` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

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

--
--  ecs_mail_templates
--
INSERT INTO `ecs_mail_templates` (`template_code`,`is_html`,`template_subject`,`template_content`,`last_modify`,`last_send`,`type`)
VALUES (
'send_sms', '0', '发送团购卷短信', '团购:{$group_name},编号:{$card_sn},密码:{$card_password},有效期至:{$past_time}', '0', '0', 'sms'
);

--
-- ecs_admin_action
--

INSERT INTO `ecs_admin_action` (`action_id`, `parent_id`, `action_code`) VALUES
(20, 0, 'group_manage'),
('', 20, 'add_group'),
('', 20, 'edit_group'),
('', 20, 'view_group'),
('', 20, 'remove_group'),
('', 20, 'download_order'),
('', 20, 'view_card'),
('', 20, 'download_card'),
('', 20, 'view_city'),
('', 20, 'add_city'),
('', 20, 'remove_city'),
('', 20, 'remove_card'),
('', 20, 'send_card'),
('', '20','importcard'),
('', '20','view_seller'),
('', '20','groupnav'),
('', 11, 'sms_templates'),
('', '4', 'importsupp'),
('', '6', 'importorder'),
('', '5', 'login_config'),
('', '11','phone_manage');

--
--  ecs_shop_config
--

INSERT INTO `ecs_shop_config` (`id`, `parent_id`, `code`, `type`, `store_range`, `store_dir`, `value`, `sort_order`) VALUES
(20, 0, 'group_config', 'group', '', '', '', '1'),
('', 6, 'formwork', 'hidden', '', '', 'meituan', '1'),
('', 20, 'group_shopname', 'text', '', '', 'ECGROUPON', '1'),
('', 20, 'group_shoptitle', 'text', '', '', '', '1'),
('', 20, 'group_shopdesc', 'text', '', '', '', '1'),
('', 20, 'group_shopaddress', 'text', '', '', '', '1'),
('', 20, 'group_city', 'hidden', '', '', '52', '1'),
('', 20, 'group_qq', 'text', '', '', '', '1'),
('', 20, 'group_email', 'text', '', '', '', '1'),
('', 20, 'group_phone', 'text', '', '', '', '1'),
('', 20, 'group_logo', 'file', '', '', 'template/meituan/images/logo.gif', '1'),
('', 20, 'group_cardname', 'text', '', '', '团购卷', '1'),
('', 20, 'left_group_num', 'text', '', '', '0', '1'),
('', 20, 'send_sms_num', 'text', '', '', '0', '1'),
('', 20, 'group_format', 'text', '', '', '¥%s', '1'),
('', 20, 'group_rewrite', 'select', '0,1', '', '0', '1'),
('', 20, 'group_comment', 'select', '0,1', '', '0', '1'),
('', 20, 'group_rebate', 'select', '0,1', '', '0', '1'),
('', '20', 'group_member_email', 'select', '0,1', '', '0', '1'),
('', 20, 'make_group_card', 'select', '0,1', '', '0', '1'),
('', 20, 'send_group_sms', 'select', '0,1', '', '0', '1'),
('','20','show_groupclass', 'manual', '1,2,3', '', '0', '1'),
('', '20', 'group_secondspay', 'select', '0,1', '', '0', '1'),
('', '20', 'topman_num', 'text', '', '', '20', '1'),
('', 20, 'group_shipping', 'manual', '', '', '', '1'),
('', 20, 'group_notice', 'textarea', '', '', '', '1'),
('' , '20', 'showmode', 'select', '0,1', '', '0', '1'),
('', '20', 'groupindex', 'select', '0,1', '', '0', '1'),
('', 20, 'group_statscode', 'textarea', '', '', '', '1');

--
-- ecs_email_list
--

ALTER TABLE `ecs_email_list` ADD `city_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0';

--
-- ecs_suppliers
--
ALTER TABLE `ecs_suppliers` ADD `east_way` VARCHAR(30) default NULL ,
ADD `west_way` VARCHAR(30) default NULL ,
ADD `user_name` VARCHAR(60) default NULL,
ADD `password` VARCHAR(32) default NULL,
ADD `website` VARCHAR(100) default NULL,
ADD `address` VARCHAR(255) default NULL,
ADD `phone` VARCHAR(20) default NULL,
ADD `linkman` VARCHAR(20) default NULL,
ADD `open_banks` VARCHAR(60) default NULL,
ADD `banks_user` VARCHAR(30) default NULL,
ADD `banks_account` VARCHAR(30) default NULL,
ADD `address_img` VARCHAR(255) default NULL,
ADD `parent_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `cid` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `is_show` TINYINT( 1 ) UNSIGNED NULL DEFAULT '0',
ADD `spread_img` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
ADD `city_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `is_ship` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `suppliers_site` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
ADD `suppliers_area` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
ADD INDEX ( `cid` ) ;
--
--  ecs_users
--
ALTER TABLE `ecs_users` ADD `city_id` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `aliss_name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

--
-- ecs_group_city 
--

INSERT INTO `ecs_group_city` (`city_id`, `city_name`,`is_open`) VALUES
(321,'上海','1'),
(52, '北京','1');


-- --------------------------------------------------------