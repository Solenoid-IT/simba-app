CREATE USER '{db_user}'@'localhost' IDENTIFIED BY '{db_pass}';

GRANT ALL PRIVILEGES ON `{db_name}`.* TO '{db_user}'@'localhost' WITH GRANT OPTION;

FLUSH PRIVILEGES;



-- SHOW GRANTS FOR '{db_user}'@'localhost';