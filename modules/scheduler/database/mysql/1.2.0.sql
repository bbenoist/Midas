-- Midas Server. Copyright Kitware SAS. Licensed under the Apache License 2.0.

-- MySQL database for the scheduler module, version 1.2.0

CREATE TABLE IF NOT EXISTS `scheduler_job` (
    `job_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `task` varchar(512) NOT NULL,
    `run_only_once` tinyint(4) NOT NULL,
    `fire_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `time_last_fired` timestamp,
    `time_interval` bigint(20),
    `priority` tinyint(4),
    `status` tinyint(4),
    `params` text,
    `creator_id` bigint(20),
    PRIMARY KEY (`job_id`)
) DEFAULT CHARSET=utf8;
