<?php

namespace Model;

class Setting
{
    use Model;

    protected $table = 'settings';

    protected $allowedColumns = [
        'setting_key',
        'setting_value',
    ];
}
