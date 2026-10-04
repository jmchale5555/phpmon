<?php

namespace Controller;

defined('ROOTPATH') or exit('Access Denied');

/**
 * home class
 */
class Home
{

    use MainController;

    public function index()
    {
        $this->view('home');
    }
}
