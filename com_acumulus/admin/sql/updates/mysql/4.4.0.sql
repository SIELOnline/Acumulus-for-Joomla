ALTER TABLE `#__acumulus_entry`
CHANGE COLUMN `entry_id` `entry_id` INT(11) NULL DEFAULT NULL,
CHANGE COLUMN `token` `token` CHAR(32) NULL DEFAULT NULL;
