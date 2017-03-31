CREATE TABLE IF NOT EXISTS `mailstream_item` (
       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
       `uid` int(11) NOT NULL,
       `contact-id` int(11) NOT NULL,
       `uri` char(255) NOT NULL,
       `message-id` char(255) NOT NULL,
       `created` timestamp NOT NULL DEFAULT now(),
       `completed` timestamp NULL DEFAULT NULL,
       PRIMARY KEY (`id`),
       KEY `message-id` (`message-id`),
       KEY `created` (`created`),
       KEY `completed` (`completed`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
