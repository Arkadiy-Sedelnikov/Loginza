CREATE TABLE IF NOT EXISTS `#__loginza_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `loginza_id` varchar(150) NOT NULL,
  `loginza_pass` varchar(100) NOT NULL,
  `confirmed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`loginza_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
