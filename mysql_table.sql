-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `sel_category`;
CREATE TABLE `sel_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `time` int(11) NOT NULL,
  `mesto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sel_keys`;
CREATE TABLE `sel_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` mediumtext,
  `id_cat` tinyint(4) NOT NULL,
  `id_subcat` tinyint(4) NOT NULL,
  `time` int(11) NOT NULL,
  `sale` tinyint(4) NOT NULL,
  `block` tinyint(4) NOT NULL,
  `block_user` int(11) NOT NULL,
  `block_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sel_orders`;
CREATE TABLE `sel_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_key` int(11) NOT NULL,
  `code` text,
  `chat` text,
  `id_subcat` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sel_qiwi`;
CREATE TABLE `sel_qiwi` (
  `iID` text,
  `sDate` text,
  `sTime` text,
  `dAmount` text,
  `iOpponentPhone` text,
  `sComment` text,
  `sStatus` text,
  `chat` text,
  `iAccount` text,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sel_set_bot`;
CREATE TABLE `sel_set_bot` (
  `token` text,
  `verification` int(11) NOT NULL,
  `block` int(11) NOT NULL,
  `hello` text NOT NULL,
  `footer` text NOT NULL,
  `proxy` text NOT NULL,
  `proxy_login` text NOT NULL,
  `proxy_pass` text NOT NULL,
  `url` text NOT NULL,
  `nomer1` text NOT NULL,
  `nomer2` text NOT NULL,
  `nomer3` text NOT NULL,
  `limits` int(11) NOT NULL,
  `on_off` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `sel_set_bot` (`token`, `verification`, `block`, `hello`, `footer`, `proxy`, `proxy_login`, `proxy_pass`, `url`, `nomer1`, `nomer2`, `nomer3`, `limits`, `on_off`) VALUES
('334343434',	10,	90,	'Вас приветствует автоматический бот продавец магазина.\r\nУ меня можно купить товар без очереди и ожидания.\r\nУкажите код нужного товара.',	'Вот и все =)',	'80.78.251.32:3129',	'201520161205',	'104tomenxxx',	'site.ru',	'777777777',	'888888',	'99999999',	2500,	'on');

DROP TABLE IF EXISTS `sel_set_qiwi`;
CREATE TABLE `sel_set_qiwi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `sel_set_qiwi` (`id`, `number`, `password`, `active`) VALUES
(1,	'79777777777',	'55556666',	1),
(2,	'22222222',	'7777722',	0),
(3,	'333333',	'77777',	0),
(4,	'4444444',	'77777',	0),
(5,	'5555555',	'77777',	0);

DROP TABLE IF EXISTS `sel_subcategory`;
CREATE TABLE `sel_subcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cat` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `amount` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mesto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sel_users`;
CREATE TABLE `sel_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` text,
  `first_name` text,
  `last_name` text,
  `chat` text,
  `time` int(11) NOT NULL,
  `id_key` int(11) NOT NULL,
  `pay_number` text NOT NULL,
  `verification` int(11) NOT NULL,
  `ban` int(1) NOT NULL DEFAULT '0',
  `cat` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2016-01-17 17:52:53
