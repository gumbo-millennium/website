<?php
/**
 * phpMyAdmin sample configuration, you can use it as base for
 * manual configuration. For easier setup you can use setup/
 *
 * All directives are explained in documentation in the doc/ folder
 * or at <https://docs.phpmyadmin.net/>.
 */

declare(strict_types=1);

$i = 1;
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['user'] = 'vscode';
$cfg['Servers'][$i]['password'] = 'vscode';
$cfg['Servers'][$i]['host'] = 'mysql';
$cfg['Servers'][$i]['only_db'] = 'vscode';
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['SendErrorReports'] = 'never';
