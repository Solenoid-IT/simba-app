USE `{db_name}`;



CREATE OR REPLACE VIEW `view::activity::all` AS
SELECT
    activity_table.*,

    user_table.`name` AS `ref.user.name`
FROM
    `activity` activity_table
        INNER JOIN
    `user` user_table
        ON
    (
        activity_table.`user` = user_table.`id`
    )
ORDER BY
    activity_table.`id` ASC
;



CREATE OR REPLACE VIEW `view::user::all` AS
SELECT
    user_table.*,

    hierarchy_table.`name` AS `ref.hierarchy.name`
FROM
    `user` user_table
        INNER JOIN
    `hierarchy` hierarchy_table
        ON
    (
        user_table.`hierarchy` = hierarchy_table.`id`
    )
ORDER BY
    user_table.`id` ASC
;



CREATE OR REPLACE VIEW `view::group::all` AS
SELECT
    group_table.*,

    user_table.`name` AS `ref.user.name`
FROM
    `group` group_table
        INNER JOIN
    `user` user_table
        ON
    (
        group_table.`owner` = user_table.`id`
    )
ORDER BY
    group_table.`id` ASC
;



CREATE OR REPLACE VIEW `view::firewall_rule::all` AS
SELECT
    firewall_rule_table.*,

    user_table.`name` AS `ref.user.name`
FROM
    `firewall_rule` firewall_rule_table
        INNER JOIN
    `user` user_table
        ON
    (
        firewall_rule_table.`owner` = user_table.`id`
    )
ORDER BY
    firewall_rule_table.`id` ASC
;



CREATE OR REPLACE VIEW `view::trigger::all` AS
SELECT
    trigger_table.*,

    user_table.`name` AS `ref.user.name`
FROM
    `trigger` trigger_table
        INNER JOIN
    `user` user_table
        ON
    (
        trigger_table.`owner` = user_table.`id`
    )
ORDER BY
    trigger_table.`id` ASC
;



CREATE OR REPLACE VIEW `view::note::share` AS
SELECT
    note_table.`id`,
    note_table.`tenant`,
    note_table.`owner`,
    user_share_rule_table.`user` AS `user`,
    NULL AS `group`,
    user_share_rule_table.`share_rule` AS `share_rule`,
    1 AS `priority`,
    note_table.`name`,
    note_table.`description`,
    note_table.`datetime.insert`,
    note_table.`datetime.update`,
    user_table_p1.`name` AS `ref.user.name`
FROM
    `note` note_table
        INNER JOIN
    `user_share_rule` user_share_rule_table
        ON
    (
        user_share_rule_table.`resource` = 1
            AND
        user_share_rule_table.`element` = note_table.`id`
    )
        INNER JOIN
    `user` user_table_p1
        ON
    (
        note_table.`owner` = user_table_p1.`id`
    )
UNION
SELECT
    note_table.`id`,
    note_table.`tenant`,
    note_table.`owner`,
    group_user_table.`user` AS `user`,
    group_share_rule_table.`group` AS `group`,
    group_share_rule_table.`share_rule` AS `share_rule`,
    2 AS `priority`,
    note_table.`name`,
    note_table.`description`,
    note_table.`datetime.insert`,
    note_table.`datetime.update`,
    user_table_p2.`name` AS `ref.user.name`
FROM
    `note` note_table
        INNER JOIN
    `group_share_rule` group_share_rule_table
        ON
    (
        group_share_rule_table.`resource` = 1
            AND
        group_share_rule_table.`element` = note_table.`id`
    )
        INNER JOIN
    `group_user` group_user_table
        ON
    (
        group_user_table.`group` = group_share_rule_table.`group`
    )
        INNER JOIN
    `user` user_table_p2
        ON
    (
        note_table.`owner` = user_table_p2.`id`
    )
;



CREATE OR REPLACE VIEW `view::session_login::active_session` AS
SELECT
    session_table.`id` AS `id`,

    session_login_table.`description` AS `description`,
    session_login_table.`ip` AS `ip`,
    session_login_table.`user_agent` AS `user_agent`,
    session_login_table.`ip_info.country.code` AS `ip_info.country.code`,
    session_login_table.`ip_info.country.name` AS `ip_info.country.name`,
    session_login_table.`ip_info.isp` AS `ip_info.isp`,

    session_login_table.`ua_info.browser` AS `ua_info.browser`,
    session_login_table.`ua_info.os` AS `ua_info.os`,
    session_login_table.`ua_info.hw` AS `ua_info.hw`,

    session_login_table.`datetime.insert` AS `datetime.insert`,

    session_table.`id` AS `session.id`,
    session_table.`user` AS `session.user`,
    session_table.`datetime.insert` AS `session.datetime.insert`,
    session_table.`datetime.update` AS `session.datetime.update`
FROM
    `session_login` session_login_table
        INNER JOIN
    `session` session_table
        ON
    (
        session_login_table.`session` = session_table.`id`
    )
WHERE
    session_table.`datetime.expiration` > CURRENT_TIMESTAMP
;