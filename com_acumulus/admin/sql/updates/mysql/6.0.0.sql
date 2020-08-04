#
# Drop and recreate index (to make it non-unique).
#
ALTER TABLE `#__acumulus_entry` DROP INDEX `idx_acumulus_entry`;
CREATE INDEX `idx_acumulus_entry` ON `#__acumulus_entry` (`entry_id`);
