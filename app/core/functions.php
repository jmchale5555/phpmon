<?php

use Core\Session;

defined('ROOTPATH') or exit('Access Denied');

/** check which php extensions are required **/
check_extensions();
function check_extensions()
{
    $required_extensions = [
        'pdo_mysql',
    ];

    $not_loaded = [];

    foreach ($required_extensions as $ext)
    {
        if (!extension_loaded($ext))
        {
            $not_loaded[] = $ext;
        }
    }

    if (!empty($not_loaded))
    {
        error_log('Missing required PHP extension(s): ' . implode(', ', $not_loaded));
        http_response_code(500);
        die('Missing required PHP extension(s): ' . implode(', ', $not_loaded));
    }
}

/** debug dump, only active in DEBUG_MODE **/
function show($stuff)
{
    if (!DEBUG_MODE)
    {
        return;
    }

    echo "<pre>";
    print_r($stuff);
    echo "</pre>";
}

/** debug dump and die, only active in DEBUG_MODE **/
function dd($stuff)
{
    if (!DEBUG_MODE)
    {
        return;
    }

    echo "<pre>";
    var_dump($stuff);
    echo "</pre>";

    die();
}

function esc($str = "")
{
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect($path)
{
    if (!preg_match('#^https?://#i', $path))
    {
        $path = ROOT . "/" . ltrim($path, "/");
    }

    header("Location: " . $path);
    die;
}

/** load image. if not exist, load placeholder **/
function get_image(mixed $file = '', string $type = 'post'): string
{
    $file = ltrim((string)($file ?? ''), '/');
    if ($file !== '' && file_exists(ROOTPATH . $file))
    {
        return ROOT . "/" . $file;
    }

    if ($type == 'user')
    {
        return ROOT . "/assets/images/user.webp";
    }
    else
    {
        return ROOT . "/assets/images/no_image.png";
    }
}

/** Adds message to session to be displayed after redirect etc **/
function message(string $msg = null, bool $clear = false)
{
    $ses = new Session();

    if (!empty($msg))
    {
        $ses->set('message', $msg);
    }
    else
    if (!empty($ses->get('message')))
    {
        $msg = $ses->get('message');

        if ($clear)
        {
            $ses->pop('message');
        }
        return $msg;
    }

    return false;
}

/** grab part of the URL (0,1,2,3 or page/section/action/id) **/
function URL($key): mixed
{
    $URL = $_GET['url'] ?? 'home';
    $URL = explode("/", trim($URL, "/"));

    switch ($key)
    {
        case 'page':
        case 0:
            return $URL[0] ?? null;
        case 'section':
        case 'slug':
        case 1:
            return $URL[1] ?? null;
        case 'action':
        case 2:
            return $URL[2] ?? null;
        case 'id':
        case 3:
            return $URL[3] ?? null;
        default:
            return null;
    }
}

/** displays input values after a page refresh **/
function old_checked(string $key, string $value, string $default = ""): string
{
    if (isset($_POST[$key]))
    {
        if ($_POST[$key] == $value)
        {
            return ' checked ';
        }
    }
    else
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET" && $default == $value)
        {
            return ' checked ';
        }
    }

    return '';
}

function old_value(string $key, mixed $default = "", string $mode = 'post'): mixed
{
    $POST = ($mode == 'post') ? $_POST : $_GET;
    if (isset($POST[$key]))
    {
        return $POST[$key];
    }

    return $default;
}

function old_select(string $key, mixed $value, mixed $default = "", string $mode = 'post'): mixed
{
    $POST = ($mode == 'post') ? $_POST : $_GET;
    if (isset($POST[$key]))
    {
        if ($POST[$key] == $value)
        {
            return " selected ";
        }
    }
    else
    if ($default == $value)
    {
        return " selected ";
    }

    return "";
}

/** returns the current CSRF token, creating one if needed **/
function csrf_token(): string
{
    $ses = new Session();
    $token = $ses->get('csrf_token');

    if (empty($token))
    {
        $token = bin2hex(random_bytes(32));
        $ses->set('csrf_token', $token);
    }

    return $token;
}

/** hidden input to drop into a form **/
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . esc(csrf_token()) . '">';
}

/** verify a submitted token (form field or X-CSRF-Token header) **/
function csrf_verify(?string $token = null): bool
{
    $ses = new Session();
    $expected = $ses->get('csrf_token');

    $token = $token ?? ($_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    return !empty($expected) && is_string($token) && hash_equals($expected, $token);
}

/** generic error handling: detailed in dev, quiet + logged in production **/
function register_error_handling(): void
{
    error_reporting(E_ALL & ~E_DEPRECATED);

    set_exception_handler(function (\Throwable $e): void
    {
        error_log(sprintf(
            '[uncaught] %s: %s in %s:%d',
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));

        if (!headers_sent())
        {
            http_response_code(500);
        }

        echo DEBUG_MODE
            ? '<pre>' . esc((string)$e) . '</pre>'
            : '<!doctype html><title>Error</title><p>An error occurred.</p>';
    });

    register_shutdown_function(function (): void
    {
        if (DEBUG_MODE)
        {
            return;
        }

        $error = error_get_last();
        if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true))
        {
            return;
        }

        error_log(sprintf('[fatal] %s in %s:%d', $error['message'], $error['file'], $error['line']));

        if (!headers_sent())
        {
            http_response_code(500);
        }

        echo '<!doctype html><title>Error</title><p>An error occurred.</p>';
    });
}
