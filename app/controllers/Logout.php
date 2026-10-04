<?php

namespace Controller;

use Core\Request;
use Core\Session;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Logout controller
 */
class Logout
{
    use MainController;

    public function index()
    {
        $req = new Request;

        if ($req->method() === 'POST')
        {
            require_csrf();
            (new Session)->logout();
        }

        redirect('home');
    }
}
