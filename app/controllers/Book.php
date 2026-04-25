<?php

namespace Controller;

use Model\Desk;
use Model\Room;
use Core\Request;
use Core\Session;
use Carbon\Carbon;
use Model\Booking;

defined('ROOTPATH') or exit('Access Denied');



/**
 * book class
 */
class Book
{

    use MainController;

    public function index($a = '', $b = '', $c = '', $d = '')
    {
        $ses = new Session;
        if (!$ses->is_logged_in())
        {
            redirect('login');
        }

        $data['name'] = empty($_SESSION['USER']) ? 'guest user' : $_SESSION['USER']->name;

        $rooms = new Room;
        $avilableRooms = $rooms->all();
        $data['rooms'] = json_encode($avilableRooms);

        $dates = [];
        for ($i = 0; $i <= 19; $i++)
        {
            $dates[] = Carbon::today()->addDays($i)->format('l\<\b\r\>d-m-Y'); // Get dates and format them as needed
        }
        $data['dates'] = json_encode($dates);

        $this->view('book', $data);
    }

    public function getDeskByRoomId($room_id)
    {
        $roomId = $room_id;
        $desks = new Desk;
        // $selectedDesks = $desks->where(['room_id' => $roomId]);
        $selectedDesks = $desks->where(['room_id' => $roomId], [], []);

        echo json_encode($selectedDesks);
    }

    public function getBookingsByRoomId($room_id)
    {
        $roomId = $room_id;
        $bookings = new Booking();
        // $selectedBookings = $bookings->where(['room_id' => $roomId]);
        $now = time() - 86400;
        $selectedBookings = $bookings->where(['room_id' => $roomId], [], ['res_date' => $now]);
        // $selectedBookings['res_date'] = getDate($selectedBookings['res_date']);

        echo json_encode($selectedBookings);
    }

    public function store()
    {

        $data = [];
        $booking = new Booking;

        // Get the JSON contents from form submission
        $data = json_decode(file_get_contents('php://input'), true);
        // $data['res_date'] = strtotime($data['res_date']);
        $data['user_id'] = $_SESSION['USER']->id;
        $data['user_name'] = $_SESSION['USER']->name;

        // use the validate and insert method to do wot it sez ont tin
        if ($booking->validate($data))
        {
            $booking->insert($data);
        }

        // echo json_encode($postData);
        echo json_encode($booking->errors);
    }


    public function deleteBookingById($bookingId)
    {
        // $data = [];
        $booking = new Booking;

        $booking->delete($bookingId);


        echo json_encode($booking->errors);
    }
}
