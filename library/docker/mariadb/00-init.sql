-- Create databases
CREATE SCHEMA IF NOT EXISTS laravel;
CREATE SCHEMA IF NOT EXISTS laravel_test;
CREATE SCHEMA IF NOT EXISTS wordpress;
CREATE SCHEMA IF NOT EXISTS wordpress_test;

-- Create users
CREATE USER IF NOT EXISTS laravel
    IDENTIFIED BY 'kiepo9Eeth0hoech5ooLa6ed8oaphaih';
CREATE USER IF NOT EXISTS corcel
    IDENTIFIED BY 'zie2doboveesh2IraiDaim1Daiku6ue9';
CREATE USER IF NOT EXISTS wordpress
    IDENTIFIED BY 'aibaiwohPaighaikaevaoThiochahX5v';

-- Grant access to tables
GRANT ALL
    ON laravel.*
    TO laravel;

GRANT ALL
    ON laravel_test.*
    TO laravel;

GRANT ALL
    ON wordpress.*
    TO wordpress;

GRANT SELECT, INSERT, UPDATE, DELETE
    ON wordpress.*
    TO corcel;

