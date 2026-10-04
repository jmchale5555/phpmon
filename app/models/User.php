<?php

namespace Model;

class User
{
    use Model;

    protected $table = 'users';

    protected $allowedColumns = [
        'name',
        'email',
        'password',
        'updated_at',
    ];

    public function validatePasswordChange(array $data, string $currentHash): bool
    {
        $this->errors = [];

        if (empty($data['current_password']))
        {
            $this->errors['current_password'] = "Current password is required";
        }
        else
        if (!password_verify((string) $data['current_password'], $currentHash))
        {
            $this->errors['current_password'] = "Current password is incorrect";
        }

        if (empty($data['password']))
        {
            $this->errors['password'] = "New password is required";
        }
        else
        if (strlen((string) $data['password']) < 8)
        {
            $this->errors['password'] = "New password must be at least 8 characters";
        }

        if (empty($data['confirm']))
        {
            $this->errors['confirm'] = "Please confirm your new password";
        }
        else
        if (!empty($data['password']) && $data['confirm'] !== $data['password'])
        {
            $this->errors['confirm'] = "Passwords do not match";
        }

        if (!empty($data['password']) && password_verify((string) $data['password'], $currentHash))
        {
            $this->errors['password'] = "New password must be different from the current one";
        }

        return empty($this->errors);
    }
}
