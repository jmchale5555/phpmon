<?php

return [
    'run' => function (PDO $pdo): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, created_at)
             VALUES (:name, :email, :password, NOW())
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                password = VALUES(password),
                updated_at = NOW()'
        );

        $stmt->execute([
            'name'     => 'Administrator',
            'email'    => 'admin@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
        ]);
    },
];
