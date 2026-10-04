<?php

return [
    'up' => function (PDO $pdo): void
    {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS media (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                path VARCHAR(255) NOT NULL,
                alt VARCHAR(255) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    },
];
