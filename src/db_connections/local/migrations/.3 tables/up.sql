USE `{db_name}`;



CREATE TABLE `hierarchy`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,
    `description`                        TEXT                                                         NULL,

    `color`                              VARCHAR(255)                                                 NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`name`)
)
;

CREATE TABLE `tenant`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`name`)
)
;

CREATE TABLE `user`
(
    `id`                                    BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                                BIGINT UNSIGNED                                          NOT NULL,
    `owner`                                 BIGINT UNSIGNED                                              NULL,

    `hierarchy`                             BIGINT UNSIGNED                                          NOT NULL,

    `name`                                  VARCHAR(255)                                             NOT NULL,

    `email`                                 VARCHAR(255)                                             NOT NULL,

    `birth.name`                            VARCHAR(255)                                                 NULL,
    `birth.surname`                         VARCHAR(255)                                                 NULL,

    `security.password`                     VARCHAR(255)                                                 NULL,
    `security.mfa`                          BOOLEAN                                    DEFAULT FALSE NOT NULL,

    `security.idk.authentication`           BOOLEAN                                    DEFAULT FALSE NOT NULL,
    `security.idk.public_key`               LONGBLOB                                                     NULL,
    `security.idk.enc_nonce`                LONGBLOB                                                     NULL,
    `security.idk.forced`                   BOOLEAN                                    DEFAULT FALSE NOT NULL,

    `security.trusted_device`               BOOLEAN                                    DEFAULT FALSE NOT NULL,

    `uuid`                                  VARCHAR(255)                                             NOT NULL,

    `datetime.insert`                       TIMESTAMP                                                NOT NULL,
    `datetime.update`                       TIMESTAMP                                                    NULL,
    `datetime.changelog_read`               TIMESTAMP                                                    NULL,
    `datetime.email_verified`               TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`tenant`,`name`),
    UNIQUE  KEY (`email`),
    UNIQUE  KEY (`uuid`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL,

    FOREIGN KEY (`hierarchy`)
    REFERENCES `hierarchy` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `group`
(
    `id`                                  BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                              BIGINT UNSIGNED                                          NOT NULL,
    `owner`                               BIGINT UNSIGNED                                              NULL,

    `name`                                VARCHAR(255)                                             NOT NULL,

    `datetime.insert`                     TIMESTAMP                                                NOT NULL,
    `datetime.update`                     TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`tenant`,`name`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
;

CREATE TABLE `group_user`
(
    `tenant`                              BIGINT UNSIGNED                                          NOT NULL,

    `group`                               BIGINT UNSIGNED                                          NOT NULL,
    `user`                                BIGINT UNSIGNED                                          NOT NULL,



    PRIMARY KEY (`group`,`user`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`group`)
    REFERENCES `group` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`user`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;



CREATE TABLE `session`
(
    `id`                                 VARCHAR(255)                                             NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                              NULL,
    `user`                               BIGINT UNSIGNED                                              NULL,

    `data`                               LONGBLOB                                                 NOT NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,
    `datetime.expiration`                TIMESTAMP                                                NOT NULL,



    PRIMARY KEY (`id`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`user`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `session_login`
(
    `session`                            VARCHAR(255)                                             NOT NULL,

    `description`                        TEXT                                                         NULL,

    `ip`                                 VARCHAR(255)                                             NOT NULL,
    `user_agent`                         LONGTEXT                                                     NULL,

    `ip_info.country.code`               VARCHAR(255)                                                 NULL,
    `ip_info.country.name`               VARCHAR(255)                                                 NULL,
    `ip_info.isp`                        VARCHAR(255)                                                 NULL,

    `ua_info.browser`                    VARCHAR(255)                                                 NULL,
    `ua_info.os`                         VARCHAR(255)                                                 NULL,
    `ua_info.hw`                         VARCHAR(255)                                                 NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,



    FOREIGN KEY (`session`)
    REFERENCES `session` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `trusted_device`
(
    `id`                                 VARCHAR(255)                                             NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,

    `owner`                              BIGINT UNSIGNED                                          NOT NULL,

    `name`                               VARCHAR(255)                                                 NULL,

    `user_agent`                         LONGTEXT                                                     NULL,

    `ua_info.browser`                    VARCHAR(255)                                                 NULL,
    `ua_info.os`                         VARCHAR(255)                                                 NULL,
    `ua_info.hw`                         VARCHAR(255)                                                 NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`owner`,`name`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `personal_token`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `owner`                              BIGINT UNSIGNED                                          NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,
    `description`                        TEXT                                                         NULL,

    `token`                              VARCHAR(255)                                             NOT NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`tenant`,`owner`,`name`),

    UNIQUE  KEY (`token`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `activity`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `user`                               BIGINT UNSIGNED                                              NULL,

    `action`                             VARCHAR(255)                                                 NULL,

    `description`                        TEXT                                                         NULL,

    `session`                            VARCHAR(255)                                                 NULL,

    `ip`                                 VARCHAR(255)                                             NOT NULL,
    `user_agent`                         LONGTEXT                                                     NULL,

    `ip_info.country.code`               VARCHAR(255)                                                 NULL,
    `ip_info.country.name`               VARCHAR(255)                                                 NULL,
    `ip_info.isp`                        VARCHAR(255)                                                 NULL,

    `ua_info.browser`                    VARCHAR(255)                                                 NULL,
    `ua_info.os`                         VARCHAR(255)                                                 NULL,
    `ua_info.hw`                         VARCHAR(255)                                                 NULL,

    `resource.type`                      VARCHAR(255)                                                 NULL,
    `resource.action`                    VARCHAR(255)                                                 NULL,
    `resource.id`                        BIGINT UNSIGNED                                              NULL,
    `resource.key`                       VARCHAR(255)                                                 NULL,

    `alert_severity`                     TINYINT UNSIGNED                                             NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.alert.read`                TIMESTAMP                                                    NULL,


    PRIMARY KEY (`id`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`user`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`session`)
    REFERENCES `session` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
;

CREATE TABLE `callable_request`
(
    `id`                                 VARCHAR(255)                                             NOT NULL,

    `request`                            LONGBLOB                                                     NULL,

    `data`                               LONGBLOB                                                     NULL,

    `callback_url`                       TEXT                                                         NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.expiration`                TIMESTAMP                                                NOT NULL,
    `datetime.authorization`             TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`)
)
;

CREATE TABLE `operation`
(
    `id`                                 VARCHAR(255)                                             NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,

    `task`                               VARCHAR(255)                                                 NULL,
    `data`                               LONGBLOB                                                     NULL,
    `display`                            TEXT                                                         NULL,
    `login`                              VARCHAR(255)                                                 NULL,
    `callback_url`                       TEXT                                                         NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.expiration`                TIMESTAMP                                                NOT NULL,
    `datetime.authorization`             TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`)
)
;

CREATE TABLE `firewall_rule`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `owner`                              BIGINT UNSIGNED                                              NULL,

    `range`                              VARCHAR(255)                                             NOT NULL,

    `description`                        TEXT                                                         NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,

    `allowed`                            BOOLEAN                            DEFAULT FALSE         NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE KEY (`tenant`,`range`),

    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
;



CREATE TABLE `resource`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`name`)
)
;

CREATE TABLE `share_rule`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`name`)
)
;



CREATE TABLE `user_share_rule`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `user`                               BIGINT UNSIGNED                                          NOT NULL,

    `resource`                           BIGINT UNSIGNED                                          NOT NULL,
    `element`                            BIGINT UNSIGNED                                          NOT NULL,

    `share_rule`                         BIGINT UNSIGNED                                          NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`user`,`resource`,`element`),
    
    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`user`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`resource`)
    REFERENCES `resource` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`share_rule`)
    REFERENCES `share_rule` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;

CREATE TABLE `group_share_rule`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,

    `resource`                           BIGINT UNSIGNED                                          NOT NULL,
    `element`                        BIGINT UNSIGNED                                          NOT NULL,

    `group`                              BIGINT UNSIGNED                                          NOT NULL,

    `share_rule`                         BIGINT UNSIGNED                                          NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`resource`,`element`,`group`),
    
    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`resource`)
    REFERENCES `resource` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`group`)
    REFERENCES `group` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    FOREIGN KEY (`share_rule`)
    REFERENCES `share_rule` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
;



CREATE TABLE `event_type`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`name`)
)
;

CREATE TABLE `trigger`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `owner`                              BIGINT UNSIGNED                                              NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,
    `description`                        TEXT                                                         NULL,

    `events`                             TEXT                                                         NULL,

    `request.method`                     VARCHAR(255)                                             NOT NULL,
    `request.url`                        LONGBLOB                                                 NOT NULL,
    `request.content`                    LONGBLOB                                                 NOT NULL,

    `response_timeout`                   INT UNSIGNED                                             NOT NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,

    `enabled`                            BOOLEAN                               DEFAULT FALSE      NOT NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`tenant`,`name`),
    
    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
;



CREATE TABLE `note`
(
    `id`                                 BIGINT UNSIGNED AUTO_INCREMENT                           NOT NULL,

    `tenant`                             BIGINT UNSIGNED                                          NOT NULL,
    `owner`                              BIGINT UNSIGNED                                              NULL,

    `name`                               VARCHAR(255)                                             NOT NULL,
    `description`                        LONGBLOB                                                     NULL,

    `datetime.insert`                    TIMESTAMP                                                NOT NULL,
    `datetime.update`                    TIMESTAMP                                                    NULL,



    PRIMARY KEY (`id`),

    UNIQUE  KEY (`tenant`,`name`),
    
    FOREIGN KEY (`tenant`)
    REFERENCES `tenant` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
    
    FOREIGN KEY (`owner`)
    REFERENCES `user` (`id`)
    ON UPDATE CASCADE
    ON DELETE SET NULL
)
;