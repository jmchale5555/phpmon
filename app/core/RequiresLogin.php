<?php

namespace Core;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Guard trait for controllers that require an authenticated session.
 */
trait RequiresLogin
{
    protected function guard(): void
    {
        $session = new Session();

        if (!$session->is_logged_in())
        {
            redirect('login');
        }
    }
}
