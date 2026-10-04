<?php

declare(strict_types=1);

/**
 * Read-only environment check for local dev and shared hosting.
 * Run with: php scripts/preflight.php
 */

if (PHP_SAPI !== 'cli')
{
    http_response_code(403);
    exit("Access denied: run this from the command line.\n");
}

$root = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

$minPhp = '8.1';
if (version_compare(PHP_VERSION, $minPhp, '<'))
{
    $errors[] = "PHP {$minPhp}+ required; found " . PHP_VERSION;
}
else
{
    $ok[] = 'PHP ' . PHP_VERSION;
}

foreach (['pdo_mysql'] as $extension)
{
    if (!extension_loaded($extension))
    {
        $errors[] = "Missing PHP extension: {$extension}";
    }
}
if (!$errors)
{
    $ok[] = 'required extensions present (pdo_mysql)';
}

if (!function_exists('putenv'))
{
    $warnings[] = "'putenv' is disabled; the .env loader will not work (use real env vars / SetEnv instead)";
}

if (ini_get('open_basedir'))
{
    $warnings[] = 'open_basedir is set: ' . ini_get('open_basedir');
}

$envFile = $root . '/.env';
if (is_file($envFile))
{
    if (is_readable($envFile))
    {
        $ok[] = '.env present and readable';
    }
    else
    {
        $errors[] = '.env exists but is not readable';
    }
}
else
{
    $warnings[] = 'No .env file; falling back to environment variables and defaults';
}

foreach (['app/core/init.php', 'public/index.php', 'public/.htaccess'] as $relative)
{
    if (!is_file($root . '/' . $relative))
    {
        $errors[] = "Missing required file: {$relative}";
    }
}

$uploads = $root . '/public/uploads';
if (!is_dir($uploads))
{
    $warnings[] = 'public/uploads/ does not exist (create it and make it writable)';
}
elseif (!is_writable($uploads))
{
    $errors[] = 'public/uploads/ is not writable by PHP';
}
else
{
    $ok[] = 'public/uploads/ exists and is writable';
}

$ok[] = is_file($root . '/vendor/autoload.php')
    ? 'vendor/ present (Composer packages will load)'
    : 'vendor/ absent (optional; the app boots without it)';

echo "preflight check\n";
foreach ($ok as $line)
{
    echo "  ok   - {$line}\n";
}
foreach ($warnings as $line)
{
    echo "  warn - {$line}\n";
}
foreach ($errors as $line)
{
    echo "  fail - {$line}\n";
}

if ($errors)
{
    echo "\npreflight FAILED (" . count($errors) . " error(s))\n";
    exit(1);
}

echo "\npreflight passed\n";
