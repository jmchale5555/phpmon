<?php

namespace Model;

class Desk
{

    use Model;

    protected $table = 'desks';

    protected $allowedColumns = [
        'room_id',
        'desk_number',

    ];
}
