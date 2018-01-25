CREATE TABLE IF NOT EXISTS `#__acumulus_entry`
(
    `id` int(11) NOT NULL auto_increment,
    `entry_id` int(11) DEFAULT NULL,
    `token` char(32) DEFAULT NULL,
    `source_type` varchar(32) NOT NULL,
    `source_id` int(11) NOT NULL,
    `created` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated` timestamp NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `idx_acumulus_entry` (`entry_id`),
    UNIQUE INDEX `idx_acumulus_source` (`source_id`, `source_type`)
);
