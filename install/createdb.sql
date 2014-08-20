/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `activity` text NOT NULL,
  `action_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `bots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `oauth_token_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_uid` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL,
  `client_version` varchar(255) NOT NULL,
  `status` enum('idle','slicing','working','paused','waiting','error','maintenance','offline','retired') DEFAULT 'idle',
  `last_seen` datetime NOT NULL,
  `manufacturer` varchar(255) NOT NULL DEFAULT '',
  `electronics` varchar(255) NOT NULL DEFAULT '',
  `firmware` varchar(255) NOT NULL DEFAULT '',
  `extruder` varchar(255) NOT NULL DEFAULT '',
  `queue_id` int(11) NOT NULL DEFAULT '0',
  `job_id` int(11) NOT NULL DEFAULT '0',
  `error_text` varchar(255) NOT NULL DEFAULT '',
  `slice_config_id` int(11) unsigned NOT NULL,
  `slice_engine_id` int(11) unsigned NOT NULL,
  `temperature_data` text NOT NULL,
  `remote_ip` varchar(255) NOT NULL,
  `local_ip` varchar(255) NOT NULL,
  `driver_name` varchar(255) NOT NULL DEFAULT 'printcore',
  `driver_config` text NOT NULL,
  `webcam_image_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `identifier` (`identifier`),
  KEY `queue_id` (`queue_id`),
  KEY `job_id` (`job_id`),
  KEY `oauth_token_id` (`oauth_token_id`),
  KEY `slice_config_id` (`slice_config_id`),
  KEY `slice_engine_id` (`slice_engine_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `content_id` int(11) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `comment_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `content_type` (`content_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL,
  `text_body` text NOT NULL,
  `html_body` text NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(255) NOT NULL,
  `queue_date` datetime NOT NULL,
  `sent_date` datetime NOT NULL,
  `status` enum('queued','sent') NOT NULL DEFAULT 'queued',
  UNIQUE KEY `id` (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `error_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `queue_id` int(11) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `error_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  KEY `bot_id` (`bot_id`),
  KEY `queue_id` (`queue_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `job_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `bot_id` int(11) NOT NULL,
  `queue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('idle','slicing','working','waiting','error','maintenance','offline', 'dropped'),
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `taken_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `bot_id` (`bot_id`),
  KEY `queue_id` (`queue_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `queue_id` int(11) unsigned NOT NULL DEFAULT '0',
  `source_file_id` int(11) unsigned NOT NULL DEFAULT '0',
  `file_id` int(11) unsigned NOT NULL DEFAULT '0',
  `slice_job_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `status` enum('available','taken','slicing','downloading','qa','complete','failure','canceled') NOT NULL DEFAULT 'available',
  `user_sort` int(11) unsigned NOT NULL DEFAULT '0',
  `bot_id` int(11) NOT NULL DEFAULT '0',
  `progress` float NOT NULL DEFAULT '0',
  `temperature_data` text NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `taken_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `downloaded_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `finished_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `slice_complete_time` datetime NOT NULL,
  `verified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `webcam_image_id` int(11) unsigned NOT NULL DEFAULT '0',
  `webcam_images` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `queue_id` (`queue_id`),
  KEY `status` (`status`),
  KEY `bot_id` (`bot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE IF NOT EXISTS `oauth_consumer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumer_key` varchar(255) NOT NULL,
  `consumer_secret` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `name` varchar(255) DEFAULT '',
  `user_id` int(11) DEFAULT '0',
  `app_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `oauth_consumer_nonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumer_id` int(11) unsigned default 0,
  `timestamp` int(11) unsigned default 0,
  `nonce` int(11) unsigned default 0,
  PRIMARY KEY (`id`),
  KEY `consumer_id` (`consumer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `oauth_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `name` text NOT NULL,
  `consumer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_secret` varchar(255) NOT NULL,
  `callback_url` text NOT NULL,
  `verifier` varchar(255) NOT NULL,
  `verified` int(11) NOT NULL,
  `device_data` text NOT NULL DEFAULT '',
  `last_seen` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `consumer_id` (`consumer_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  KEY `ip_address` (`ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `queues` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `s3_files` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `size` int(10) unsigned NOT NULL,
  `hash` char(32) NOT NULL,
  `bucket` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `add_date` datetime NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL,
  `source_url` text,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `shortcodes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `slice_configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fork_id` int(11) unsigned NOT NULL,
  `engine_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `config_name` varchar(255) NOT NULL,
  `config_data` text NOT NULL,
  `add_date` datetime NOT NULL,
  `edit_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fork_id` (`fork_id`),
  KEY `user_id` (`user_id`),
  KEY `engine_id` (`engine_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `slice_engines` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `engine_name` varchar(255) NOT NULL,
  `engine_path` varchar(255) NOT NULL,
  `is_featured` tinyint(1) NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `add_date` datetime NOT NULL,
  `default_config_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `engine_name` (`engine_name`),
  KEY `is_featured` (`is_featured`),
  KEY `is_public` (`is_public`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `engine_os` (
  `engine_id` int(11) unsigned NOT NULL,
  `os` enum('osx','linux','win','raspberrypi'),
  PRIMARY KEY (`engine_id`, `os`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `slice_jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `input_id` int(11) unsigned NOT NULL,
  `output_id` int(11) unsigned NOT NULL,
  `output_log` text NOT NULL,
  `error_log` text,
  `slice_config_id` int(11) unsigned NOT NULL,
  `slice_config_snapshot` text NOT NULL,
  `status` enum('available','slicing','pending','complete','failure','expired') DEFAULT 'available',
  `progress` float NOT NULL DEFAULT '0',
  `add_date` datetime NOT NULL,
  `taken_date` datetime NOT NULL,
  `finish_date` datetime NOT NULL,
  `uid` char(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  KEY `slice_config_id` (`slice_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `hash` varchar(40) NOT NULL,
  `expire_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pass_hash` (`hash`),
  KEY `expire_date` (`expire_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass_hash` varchar(40) NOT NULL,
  `pass_reset_hash` char(40) NOT NULL,
  `location` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  `last_active` datetime NOT NULL,
  `registered_on` datetime NOT NULL,
  `dashboard_style` enum('list','large_thumbnails','medium_thumbnails','small_thumbnails') NOT NULL DEFAULT 'large_thumbnails',
  `thingiverse_token` varchar(40) NOT NULL DEFAULT '',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `last_active` (`last_active`),
  KEY `username` (`username`),
  KEY `pass_hash` (`pass_hash`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = @saved_cs_client */;
CREATE TABLE IF NOT EXISTS `patches` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `patch_num` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patch_num` (`patch_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO patches(patch_num, description) VALUES(9, 'Adding dropped to the job_clock');
