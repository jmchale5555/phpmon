<?php

namespace Model;

class Booking
{

    use Model;

    protected $table = 'bookings';

    protected $allowedColumns = [
        'user_id',
        'desk_id',
        'room_id',
        'res_date',
        'user_name'
    ];

    public function validate($data)
    {
        $this->errors = [];

        if (empty($data['user_id']))
        {
            $this->errors['user_id'] = "User ID is required";
        }
        else
        if (empty($data['desk_id']))
        {
            $this->errors['desk_id'] = "Desk ID is required";
        }
        else
        if (empty($data['room_id']))
        {
            $this->errors['room_id'] = "Room ID is required";
        }
        else
        if (empty($data['res_date']))
        {
            $this->errors['res_date'] = "Booking date is required";
        }

        if (empty($this->errors))
        {
            return true;
        }

        return false;
    }
}
