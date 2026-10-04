<?php

namespace Controller;

use Model\User;
use Core\Request;
use Core\Session;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Login controller
 */
class Login
{
    use MainController;

    public function index()
    {
        $session = new Session;

        if ($session->is_logged_in())
        {
            redirect('admin');
        }

        $data = ['errors' => []];
        $req  = new Request;

        if ($req->posted())
        {
            require_csrf();

            $user = new User;
            $row  = $user->first(['email' => trim((string) $req->post('email'))]);

            if ($row && password_verify((string) $req->post('password'), (string) $row->password))
            {
                unset($row->password);
                $session->regenerate();
                $session->auth($row);
                redirect('admin');
            }

            $data['errors'] = ['email' => 'Wrong email or password'];
        }

        $this->view('login', $data);
    }
}
