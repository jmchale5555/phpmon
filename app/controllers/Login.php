<?php

namespace Controller;

use Model\User;
use Core\Session;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Room class
 */
class Login
{
    use MainController;

    public function index()
    {
        $data = [];

        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $user = new User;
            $arr['email'] = $_POST['email'];

            $row = $user->first($arr);

            if ($row)
            {
                if (password_verify($_POST['password'], $row->password))
                {
                    $session = new Session;
                    $session->auth($row);
                    redirect('book');
                }
            }

            $user->errors['email'] = "Wrong email or password";

            $data['errors'] = $user->errors;
        }

        $this->view('login', $data);
    }
}
