<?php

return [
    'run' => function (PDO $pdo): void
    {
        $settings = [
            'site_title'    => 'My Business',
            'footer_text'   => 'Your footer text here.',
            'contact_email' => 'hello@example.com',
            'contact_phone' => '01234 567890',
            'address'       => '1 Example Street, Exampleton',
        ];

        $stmt = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        foreach ($settings as $key => $value)
        {
            $stmt->execute(['setting_key' => $key, 'setting_value' => $value]);
        }
    },
];
