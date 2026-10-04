<?php

namespace Controller;

use Model\User;
use Core\Request;
use Core\Session;
use Core\RequiresLogin;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Change password controller
 */
class Password
{
    use MainController;
    use RequiresLogin;

    public function index()
    {
        $this->guard();

        $session = new Session;
        $req     = new Request;
        $user    = new User;

        $row = $user->first(['id' => (int) $session->user('id')]);
        if (!$row)
        {
            $session->logout();
            redirect('login');
        }

        $data = ['errors' => []];

        if ($req->posted())
        {
            require_csrf();

            if ($user->validatePasswordChange($req->post(), (string) $row->password))
            {
                $user->update((int) $row->id, [
                    'password'   => password_hash((string) $req->post('password'), PASSWORD_BCRYPT),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                message('Password updated.');
                redirect('password');
            }

            $data['errors'] = $user->errors;
        }

        $this->view('password', $data);
    }
}
