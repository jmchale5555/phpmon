<?php

session_start();

/**  Valid PHP Version? **/
$minPHPVersion = '8.1';
if (phpversion() < $minPHPVersion)
{
    die("Your PHP version must be {$minPHPVersion} or higher to run this app. Your current version is " . phpversion());
}

/** Absolute paths. ROOTPATH is the public web root; APPROOT is the project root. **/
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPROOT', dirname(ROOTPATH) . DIRECTORY_SEPARATOR);

/** Optional Composer autoloader. Only needed if you add packages (e.g. nesbot/carbon). **/
$composerAutoload = APPROOT . 'vendor/autoload.php';
if (is_file($composerAutoload))
{
    require $composerAutoload;
}

require APPROOT . 'app/core/init.php';

DEBUG_MODE ? ini_set('display_errors', '1') : ini_set('display_errors', '0');

$app = new App;
$app->loadController();
