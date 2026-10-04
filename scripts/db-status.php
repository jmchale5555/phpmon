<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

function table_exists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table_name'
    );
    $stmt->execute(['table_name' => $tableName]);

    return (int)$stmt->fetchColumn() > 0;
}

$pdo = db_connect();

echo "database status\n";

foreach (['schema_migrations' => 'migrations', 'schema_seeds' => 'seeders'] as $table => $label)
{
    echo "{$table} table: " . (table_exists($pdo, $table) ? 'present' : 'missing') . "\n";
}

if (table_exists($pdo, 'schema_migrations'))
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM schema_migrations')->fetchColumn();
    echo "applied migrations: {$count}\n";
}

if (table_exists($pdo, 'schema_seeds'))
{
    $count = (int)$pdo->query('SELECT COUNT(*) FROM schema_seeds')->fetchColumn();
    echo "applied seeders: {$count}\n";
}
