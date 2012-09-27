ALTER TABLE bots add `slice_config_id` int(11) unsigned NOT NULL;
ALTER TABLE bots add key `slice_config_id` (`slice_config_id`);

CREATE TABLE `slice_engines` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `engine_name` varchar(255) NOT NULL,
  `engine_path` varchar(255) NOT NULL,
  `engine_description` text NOT NULL,
  `is_featured` tinyint(1)  NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `add_date` datetime NOT NULL,
  `default_config_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `engine_name` (`engine_name`),
  KEY `is_featured` (`is_featured`),
  KEY `is_public` (`is_public`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `slice_configs` (
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

CREATE TABLE `slice_jobs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `job_id` int(11) unsigned NOT NULL,
  `input_id` int(11) unsigned NOT NULL,
  `output_id` int(11) unsigned NOT NULL,
  `output_log` text NOT NULL,
  `slice_config_id` int(11) unsigned NOT NULL,
  `slice_config_snapshot` text NOT NULL,
  `worker_token` char(40) NOT NULL,
  `worker_name` varchar(255) NOT NULL,
  `status` enum('available', 'slicing', 'complete', 'failure'),
  `progress` float NOT NULL DEFAULT '0',
  `add_date` datetime NOT NULL,
  `taken_date` datetime NOT NULL,
  `finish_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `job_id` (`job_id`),
  KEY `slice_config_id` (`slice_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table slice_configs add start_gcode text not null after config_data;
alter table slice_configs add end_gcode text not null after start_gcode;

alter table jobs modify status enum('available','taken','slicing','downloading','qa','complete','failure') default 'available';
alter table jobs add source_file_id int(11) unsigned not null default 0 after queue_id;
alter table jobs add slice_job_id int(11) unsigned not null default 0 after file_id;
alter table jobs add slice_complete_time datetime not null after taken_time;
alter table slice_jobs add error_log text after output_log;