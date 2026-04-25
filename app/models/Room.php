<?php

namespace Model;

class Room
{

    use Model;

    // protected $limit         = 10;
    // protected $offset         = 0;
    // protected $order_type     = "desc";
    // protected $order_column = "id";
    // public $errors         = [];

    protected $table = 'rooms';

    protected $allowedColumns = [
        'id',
        'room_name',

    ];
}
