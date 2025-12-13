USE `{db_name}`;



CREATE EVENT `{db_name}`.`event::callable_request::clean`
ON SCHEDULE EVERY 1 HOUR
STARTS '2025-01-01 00:00:00'
DO
    DELETE
    FROM
        `{db_name}`.`callable_request`
    WHERE
        CURRENT_TIMESTAMP >= `datetime.expiration`
;



CREATE EVENT `{db_name}`.`event::operation::clean`
ON SCHEDULE EVERY 1 HOUR
STARTS '2025-01-01 00:00:00'
DO
    DELETE
    FROM
        `{db_name}`.`operation`
    WHERE
        CURRENT_TIMESTAMP >= `datetime.expiration` + INTERVAL 1 MONTH
;



CREATE EVENT `{db_name}`.`event::session::clean`
ON SCHEDULE EVERY 1 HOUR
STARTS '2023-12-12 00:00:00'
DO
    DELETE
    FROM
        `{db_name}`.`session`
    WHERE
        CURRENT_TIMESTAMP >= `datetime.expiration`
;