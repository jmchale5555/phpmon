<?php

namespace Controller;

use Model\Room;
use Model\User;
use Core\Request;
use Core\Session;

defined('ROOTPATH') or exit('Access Denied');

/**
 * room class
 */
class RoomController
{

    use MainController;

    public function rooms($a = '', $b = '', $c = '', $d = '')
    {

        $roomModel = new Room;
        $rooms = $roomModel->all();
        return $rooms;
    }

    public function desks()
    {
        // placeholder for function
    }

    public function index()
    {
        $ses = new Session;
        if (!$ses->is_logged_in())
        {
            die;
        }

        $req = new Request;
        $user = new User();
        $info['success'] = false;
        $info['message'] = "";

        if ($req->posted())
        {
        }
    }
}
