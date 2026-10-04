<?php

class App
{

    private $controller = 'Home';
    private $method = 'index';

    private function splitURL()
    {
        $URL = $_GET['url'] ?? 'home';
        $URL = array_values(array_filter(explode('/', trim($URL, '/')), function ($segment)
        {
            return $segment !== '';
        }));

        return $URL ?: ['home'];
    }

    public function loadController()
    {
        $URL = $this->splitURL();

        // ** select controller based on first url parameter
        $filename = APPROOT . "app/controllers/" . ucfirst($URL[0]) . ".php";
        if (file_exists($filename))
        {
            require_once $filename;
            $this->controller = ucfirst($URL[0]);
            unset($URL[0]);
        }
        else
        {
            $filename = APPROOT . "app/controllers/_404.php";
            require_once $filename;
            $this->controller = '_404';
        }

        $controller = new ('\Controller\\' . $this->controller);

        // ** select method based on second url parameter
        if (!empty($URL[1]))
        {
            $requestedMethod = $URL[1];
            $isMethodAllowed = strncmp($requestedMethod, '_', 1) !== 0
                && is_callable([$controller, $requestedMethod]);

            if ($isMethodAllowed)
            {
                $this->method = $requestedMethod;
                unset($URL[1]);
            }
            else
            {
                require_once APPROOT . "app/controllers/_404.php";
                $controller = new \Controller\_404;
                $this->method = 'index';
            }
        }

        call_user_func_array([$controller, $this->method], array_values($URL));
    }
}
