ALTER TABLE bots add `slice_config_id` int(11) unsigned NOT NULL;

CREATE TABLE `slice_engines` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `engine_name` int(11) unsigned NOT NULL,
  `engine_version` int(11) unsigned NOT NULL,
  `engine_description` int(11) unsigned NOT NULL,
  `is_featured` int(11) unsigned NOT NULL,
  `is_public` int(11) unsigned NOT NULL,
  `add_date` datetime NOT NULL,
  `default_config_id` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `slice_configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `engine_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `config_name` int(11) unsigned NOT NULL,
  `add_date` datetime NOT NULL,
  `edit_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `engine_id` (`engine_id`),
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `slice_jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `input_id` int(11) unsigned NOT NULL,
  `output_id` int(11) unsigned NOT NULL,
  `slice_config_id` int(11) unsigned NOT NULL,
  `slice_config_snapshot` text NOT NULL,
  `worker_token` char(40) NOT NULL,
  `output_log` text NOT NULL,
  `status` enum('available', 'taken', 'complete', 'failure'),
  `progress` float NOT NULL DEFAULT '0',
  `add_date` datetime NOT NULL,
  `taken_date` datetime NOT NULL,
  `finish_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `engine_id` (`engine_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;