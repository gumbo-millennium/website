-- Create databases
CREATE SCHEMA IF NOT EXISTS laravel
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE SCHEMA IF NOT EXISTS laravel_test
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Create users
CREATE USER IF NOT EXISTS laravel
    IDENTIFIED BY 'kiepo9Eeth0hoech5ooLa6ed8oaphaih';
CREATE USER IF NOT EXISTS phpmyadmin
    IDENTIFIED BY 'etah2raith4Thee5eijeiYei(f3foh8I';

-- Grant Laravel all access to their own databases
GRANT ALL
    ON laravel.*
    TO laravel;

GRANT ALL
    ON laravel_test.*
    TO laravel;

-- Restrict phpMyAdmin user to data-only access
GRANT SELECT, INSERT, UPDATE, DELETE
    ON laravel.*
    TO phpmyadmin;
