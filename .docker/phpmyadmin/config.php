<?php
/**
 * Config for PhpMyAdmin
 *
 * @category Config
 * @package  PhpMyAdmin
 * @author   Roelof Roos <github@roelof.io>
 * @link     https://docs.phpmyadmin.net/en/latest/config.html
 */
$cfg['SendErrorReports'] = 'always';

// Hide some tables
$cfg['Servers'][1]['hide_db'] = '^(information_schema|phpmyadmin|mysql|laravel_test)$';

