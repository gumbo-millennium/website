-- Create databases
CREATE SCHEMA IF NOT EXISTS laravel
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_unicode_ci;

CREATE SCHEMA IF NOT EXISTS laravel_test
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_unicode_ci;

CREATE SCHEMA IF NOT EXISTS wordpress
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_unicode_ci;

CREATE SCHEMA IF NOT EXISTS wordpress_test
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_unicode_ci;

-- Create users
CREATE USER IF NOT EXISTS laravel
    IDENTIFIED BY 'kiepo9Eeth0hoech5ooLa6ed8oaphaih';
CREATE USER IF NOT EXISTS corcel
    IDENTIFIED BY 'zie2doboveesh2IraiDaim1Daiku6ue9';
CREATE USER IF NOT EXISTS wordpress
    IDENTIFIED BY 'aibaiwohPaighaikaevaoThiochahX5v';
CREATE USER IF NOT EXISTS phpmyadmin
    IDENTIFIED BY 'etah2raith4Thee5eijeiYei(f3foh8I';

-- Grant Laravel all access to their own databases
GRANT ALL
    ON laravel.*
    TO laravel;

GRANT ALL
    ON laravel_test.*
    TO laravel;

-- Grant WordPress all access to their own database
GRANT ALL
    ON wordpress.*
    TO wordpress;

-- Restrict Corcel WordPress user to data-only access
GRANT SELECT, INSERT, UPDATE, DELETE
    ON wordpress.*
    TO corcel;

-- Restrict phpMyAdmin user to data-only access
GRANT SELECT, INSERT, UPDATE, DELETE
    ON wordpress.*
    TO phpmyadmin;
GRANT SELECT, INSERT, UPDATE, DELETE
    ON laravel.*
    TO phpmyadmin;
