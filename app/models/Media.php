<?php

namespace Model;

class Media
{
    use Model;

    protected $table = 'media';

    protected $allowedColumns = [
        'path',
        'alt',
    ];
}
