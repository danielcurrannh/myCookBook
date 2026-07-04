CREATE USER IF NOT EXISTS 'usr_myCookBook'@'localhost' IDENTIFIED BY 'ChangeThisPassword123!';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES
ON db_myCookBook.*
TO 'usr_myCookBook'@'localhost';

FLUSH PRIVILEGES;

