-- Create databases
CREATE SCHEMA IF NOT EXISTS laravel
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE SCHEMA IF NOT EXISTS laravel_test
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE SCHEMA IF NOT EXISTS phpmyadmin
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Drop users if they exist
DROP USER IF EXISTS laravel;
DROP USER IF EXISTS phpmyadmin;

-- Create users with old-skool auth form
CREATE USER laravel
    IDENTIFIED
        BY 'laravel';
CREATE USER phpmyadmin
    IDENTIFIED
        BY 'phpmyadmin';

-- Flush roles
FLUSH PRIVILEGES;

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

GRANT ALL
    ON phpmyadmin.*
    TO phpmyadmin;
