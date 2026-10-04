<?php

namespace Model;

class Page
{
    use Model;

    protected $table = 'pages';

    protected $allowedColumns = [
        'slug',
        'title',
        'content',
        'is_published',
        'updated_at',
    ];
}
