CREATE USER 'simba'@'localhost' IDENTIFIED BY 'pass';

GRANT ALL PRIVILEGES ON `simba_app`.* TO 'simba'@'localhost' WITH GRANT OPTION;

FLUSH PRIVILEGES;



-- SHOW GRANTS FOR 'simba'@'localhost';