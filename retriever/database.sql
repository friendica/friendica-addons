CREATE TABLE IF NOT EXISTS `retriever_rule` (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
       `uid` int(11) NOT NULL,
       `contact-id` int(11) NOT NULL,
       `data` mediumtext NULL DEFAULT NULL,
       PRIMARY KEY (`id`),
       KEY `uid` (`uid`),
       KEY `contact-id` (`contact-id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `retriever_item` (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
       `item-uri` varchar(800) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
       `item-uid` int(10) unsigned NOT NULL DEFAULT '0',
       `contact-id` int(10) unsigned NOT NULL DEFAULT '0',
       `resource` int(11) NOT NULL,
       `finished` tinyint(1) unsigned NOT NULL DEFAULT '0',
       KEY `resource` (`resource`),
       KEY `finished` (`finished`),
       KEY `item-uid` (`item-uid`),
       KEY `all` (`item-uri`, `item-uid`, `contact-id`),
       PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `retriever_resource` (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
       `item-uid` int(10) unsigned NOT NULL DEFAULT '0',
       `contact-id` int(10) unsigned NOT NULL DEFAULT '0',
       `type` char(255) NULL DEFAULT NULL,
       `binary` int(1) NOT NULL DEFAULT 0,
       `url` varchar(800) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
       `created` timestamp NOT NULL DEFAULT now(),
       `completed` timestamp NULL DEFAULT NULL,
       `last-try` timestamp NULL DEFAULT NULL,
       `num-tries` int(11) NOT NULL DEFAULT 0,
       `data` mediumblob NULL DEFAULT NULL,
       `http-code` smallint(1) unsigned NULL DEFAULT NULL,
       `redirect-url` varchar(800) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
       KEY `url` (`url`),
       KEY `completed` (`completed`),
       PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin
