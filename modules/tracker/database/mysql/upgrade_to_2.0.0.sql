-- Midas Server. Copyright Kitware SAS. Licensed under the Apache License 2.0.

CREATE TABLE IF NOT EXISTS `tracker_submission2item` (
  `submission_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `label` varchar(255) NOT NULL,
  `trendgroup_id` bigint(20) NOT NULL,
  KEY (`submission_id`),
  KEY (`item_id`),
  KEY (`trendgroup_id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tracker_submissionparam` (
  `param_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `submission_id` bigint(20) NOT NULL,
  `param_name` varchar(255) NOT NULL,
  `param_type` enum('text', 'numeric') NOT NULL,
  `text_value` text,
  `numeric_value` double,
  PRIMARY KEY (`param_id`),
  KEY (`submission_id`),
  KEY (`param_name`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tracker_trendgroup` (
  `trendgroup_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `producer_id` bigint(20) NOT NULL,
  `config_item_id` bigint(20),
  `test_dataset_id` bigint(20),
  `truth_dataset_id` bigint(20),
  PRIMARY KEY (`trendgroup_id`),
  KEY (`producer_id`),
  KEY (`config_item_id`),
  KEY (`test_dataset_id`),
  KEY (`truth_dataset_id`)
) DEFAULT CHARSET=utf8;


DROP PROCEDURE IF EXISTS `create_submissions`;
DROP PROCEDURE IF EXISTS `migrate_items_to_submissions`;
DROP PROCEDURE IF EXISTS `migrate_params`;
DROP PROCEDURE IF EXISTS `scalar_to_submission`;
DROP PROCEDURE IF EXISTS `create_trendgroups`;

DELIMITER '$$'
SOURCE create_submissions.sql
SOURCE migrate_items_to_submissions.sql
SOURCE migrate_params.sql
SOURCE scalar_to_submission.sql
SOURCE create_trendgroups.sql
DELIMITER ';'

ALTER TABLE tracker_submission ADD COLUMN `producer_revision` VARCHAR(255);
ALTER TABLE tracker_submission ADD COLUMN `user_id` bigint(20) NOT NULL DEFAULT '-1';
ALTER TABLE tracker_submission ADD COLUMN `official` tinyint(4) NOT NULL DEFAULT '1';
ALTER TABLE tracker_submission ADD COLUMN `build_results_url` text NOT NULL;
ALTER TABLE tracker_submission ADD COLUMN `branch` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE tracker_submission ADD COLUMN `extra_urls` text;
ALTER TABLE tracker_submission ADD COLUMN `reproduction_command` text;

ALTER TABLE tracker_submission ADD KEY (`user_id`);
ALTER TABLE tracker_submission ADD KEY(`submit_time`);
ALTER TABLE tracker_submission ADD KEY (`branch`);

ALTER TABLE tracker_trend ADD COLUMN `trendgroup_id` bigint(20) NOT NULL DEFAULT '-1';
ALTER TABLE tracker_trend ADD KEY (`trendgroup_id`);

CALL create_submissions();

CALL migrate_params();

CALL create_trendgroups();

CALL migrate_items_to_submissions();

CALL scalar_to_submission();

DROP TABLE IF EXISTS tracker_param;

RENAME TABLE tracker_submissionparam TO tracker_param;

ALTER TABLE tracker_scalar CHANGE `submission_id` `submission_id` bigint(20) NOT NULL;
ALTER TABLE tracker_scalar
    DROP COLUMN `producer_revision`,
    DROP COLUMN `user_id`,
    DROP COLUMN `official`,
    DROP COLUMN `build_results_url`,
    DROP COLUMN `branch`,
    DROP COLUMN `extra_urls`,
    DROP COLUMN `reproduction_command`,
    DROP COLUMN `submit_time`;

DROP TABLE IF EXISTS `tracker_scalar2item`;

ALTER TABLE tracker_trend
    DROP COLUMN `producer_id`,
    DROP COLUMN `config_item_id`,
    DROP COLUMN `test_dataset_id`,
    DROP COLUMN `truth_dataset_id`;
