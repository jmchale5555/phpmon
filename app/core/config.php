<?php

/**
 * Application configuration.
 *
 * Values come from real environment variables first, then from a project-root
 * .env file (see .env.example), then from the defaults below. This keeps the
 * app runnable in Docker (env injected by compose) and on shared hosting
 * (values in .env) without any extra dependency.
 */

$envFile = dirname(__DIR__, 2) . '/.env';

if (is_file($envFile))
{
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
    {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '='))
        {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        $length = strlen($value);
        if ($length >= 2)
        {
            $first = $value[0];
            $last  = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'"))
            {
                $value = substr($value, 1, -1);
            }
        }

        if ($key !== '' && getenv($key) === false)
        {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

$dbName = getenv('DB_NAME') ?: 'phpmon';
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$appUrl  = getenv('APP_URL') ?: 'http://localhost:8080';
$appName = getenv('APP_NAME') ?: 'My App';
$appDesc = getenv('APP_DESC') ?: 'A no-build PHP MVC monolith';
$debugMode = filter_var(getenv('DEBUG_MODE') ?: 'false', FILTER_VALIDATE_BOOLEAN);

define('DBNAME', $dbName);
define('DBHOST', $dbHost);
define('DBPORT', $dbPort);
define('DBUSER', $dbUser);
define('DBPASS', $dbPass);

define('ROOT', rtrim($appUrl, '/'));

define('APP_NAME', $appName);
define('APP_DESC', $appDesc);

define('DEBUG_MODE', $debugMode);
