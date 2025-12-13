USE `{db_name}`;



INSERT IGNORE INTO `hierarchy` (`id`, `name`, `description`, `color`, `datetime.insert`) VALUES
(1, 'admin', '<b>FLUID</b> for every resource + <b>FLUID</b> for users', '#b37528', CURRENT_TIMESTAMP),
(2, 'manager', '<b>FLUID</b> for every resource', '#4b884b', CURRENT_TIMESTAMP),
(3, 'viewer', '<b>FL</b> for every resource', '#5c9ad0', CURRENT_TIMESTAMP)
;



INSERT IGNORE INTO `share_rule` (`id`, `name`) VALUES
(1, 'full_access'),
(2, 'read_only')
;



INSERT IGNORE INTO `resource` (`id`, `name`) VALUES
(1, 'note')
;