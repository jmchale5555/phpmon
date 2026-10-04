<?php

namespace Controller;

use Model\Page as PageModel;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Public content pages.
 *
 * The framework router maps /page to index() and /page/about to a method named
 * "about". __call() turns that method name into the page slug, which gives
 * clean URLs without a dynamic route table.
 */
class Page
{
    use MainController;

    public function index()
    {
        $this->render('home');
    }

    public function __call($name, $arguments)
    {
        $this->render((string) $name);
    }

    private function render(string $slug): void
    {
        $page = (new PageModel)->first(['slug' => $slug, 'is_published' => 1]);

        if (!$page)
        {
            http_response_code(404);
            $this->view('404');
            return;
        }

        $content = json_decode((string) $page->content, true);
        if (!is_array($content))
        {
            $content = [];
        }

        $this->view('page', ['page' => $page, 'content' => $content]);
    }
}
