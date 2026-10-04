<?php

namespace Controller;

use Model\Page as PageModel;
use Model\Setting;
use Model\Media;
use Core\Request;
use Core\RequiresLogin;

defined('ROOTPATH') or exit('Access Denied');

/**
 * Admin controller: dashboard, pages, settings, media.
 */
class Admin
{
    use MainController;
    use RequiresLogin;

    public function index()
    {
        $this->guard();

        $data = [
            'pages'    => (new PageModel)->count(),
            'media'    => (new Media)->count(),
            'settings' => (new Setting)->count(),
        ];

        $this->view('admin_dashboard', $data);
    }

    public function pages()
    {
        $this->guard();

        $data['pages'] = (new PageModel)->all();

        $this->view('admin_pages', $data);
    }

    public function editPage($id = null)
    {
        $this->guard();

        $model = new PageModel;
        $req   = new Request;
        $page  = $id !== null ? $model->first(['id' => (int) $id]) : null;

        if ($id !== null && !$page)
        {
            http_response_code(404);
            $this->view('404');
            return;
        }

        $errors = [];

        if ($req->posted())
        {
            require_csrf();

            $title      = trim((string) $req->post('title'));
            $slug       = trim((string) $req->post('slug'));
            $contentRaw = (string) $req->post('content', '{}');
            $published  = $req->post('is_published') ? 1 : 0;

            if ($title === '')
            {
                $errors['title'] = 'Title is required';
            }

            $slug = $this->slugify($slug !== '' ? $slug : $title);
            if ($slug === '')
            {
                $errors['slug'] = 'Slug is required';
            }

            $decoded = json_decode($contentRaw, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded))
            {
                $errors['content'] = 'Content must be valid JSON';
            }

            if (empty($errors))
            {
                $existing = $model->first(['slug' => $slug]);
                if ($existing && (!$page || (int) $existing->id !== (int) $page->id))
                {
                    $errors['slug'] = 'That slug is already in use';
                }
            }

            if (empty($errors))
            {
                $input = [
                    'title'        => $title,
                    'slug'         => $slug,
                    'content'      => json_encode($decoded),
                    'is_published' => $published,
                    'updated_at'   => date('Y-m-d H:i:s'),
                ];

                if ($page)
                {
                    $model->update((int) $page->id, $input);
                }
                else
                {
                    $model->insert($input);
                }

                message('Page saved.');
                redirect('admin/pages');
            }
        }

        $this->view('admin_page_edit', ['page' => $page, 'errors' => $errors]);
    }

    public function deletePage($id = null)
    {
        $this->guard();
        require_csrf();

        $model = new PageModel;
        $row   = $id !== null ? $model->first(['id' => (int) $id]) : false;

        if ($row)
        {
            $model->delete((int) $row->id);
            message('Page deleted.');
        }

        redirect('admin/pages');
    }

    public function settings()
    {
        $this->guard();

        $model = new Setting;
        $req   = new Request;

        if ($req->posted())
        {
            require_csrf();

            $values = $req->post('settings');
            if (is_array($values))
            {
                foreach ($values as $key => $value)
                {
                    $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) $key));
                    if ($key === '')
                    {
                        continue;
                    }

                    $row = $model->first(['setting_key' => $key]);
                    if ($row)
                    {
                        $model->update((int) $row->id, ['setting_value' => (string) $value]);
                    }
                    else
                    {
                        $model->insert(['setting_key' => $key, 'setting_value' => (string) $value]);
                    }
                }
            }

            message('Settings saved.');
            redirect('admin/settings');
        }

        $this->view('admin_settings', ['settings' => $model->all()]);
    }

    public function media()
    {
        $this->guard();

        $model  = new Media;
        $req    = new Request;
        $errors = [];

        if ($req->posted())
        {
            require_csrf();

            $error = $this->storeUpload($req->files('image'));
            if ($error !== null)
            {
                $errors['image'] = $error;
            }
            else
            {
                message('Image uploaded.');
                redirect('admin/media');
            }
        }

        $this->view('admin_media', ['media' => $model->all(), 'errors' => $errors]);
    }

    public function deleteMedia($id = null)
    {
        $this->guard();
        require_csrf();

        $model = new Media;
        $row   = $id !== null ? $model->first(['id' => (int) $id]) : false;

        if ($row)
        {
            $path = ROOTPATH . $row->path;
            if (is_file($path))
            {
                @unlink($path);
            }

            $model->delete((int) $row->id);
            message('Image deleted.');
        }

        redirect('admin/media');
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /** Validate and store an uploaded image; returns an error string or null. */
    private function storeUpload($file): ?string
    {
        if (!is_array($file) || empty($file['tmp_name']))
        {
            return 'No file uploaded.';
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
        {
            return 'Upload failed.';
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024)
        {
            return 'File is too large (max 5 MB).';
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false)
        {
            return 'That file is not a valid image.';
        }

        $types = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        ];

        if (!isset($types[$info[2]]))
        {
            return 'Unsupported image type.';
        }

        $dir = ROOTPATH . 'uploads';
        if (!is_dir($dir) && !mkdir($dir, 0755, true))
        {
            return 'Could not create the uploads directory.';
        }

        $name = bin2hex(random_bytes(8)) . '.' . $types[$info[2]];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name))
        {
            return 'Could not save the uploaded file.';
        }

        (new Media)->insert(['path' => 'uploads/' . $name, 'alt' => '']);

        return null;
    }
}
