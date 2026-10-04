<?php

return [
    'run' => function (PDO $pdo): void
    {
        $content = json_encode([
            'hero_title' => 'Welcome to our website',
            'hero_text'  => 'We are a small business. Replace this text from the admin area.',
            'about_text' => 'Tell your story here. Every block you add in the editor becomes a section.',
        ]);

        $stmt = $pdo->prepare(
            'INSERT INTO pages (slug, title, content, is_published, created_at)
             VALUES (:slug, :title, :content, 1, NOW())
             ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                content = VALUES(content),
                updated_at = NOW()'
        );

        $stmt->execute([
            'slug'    => 'home',
            'title'   => 'Home',
            'content' => $content,
        ]);
    },
];
