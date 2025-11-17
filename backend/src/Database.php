<?php
declare(strict_types=1);

final class Database
{
    private PDO $pdo;

    public function __construct(string $path)
    {
        $needsInit = !file_exists($path);
        // SQLite embarqué pour stocker les résultats déjà vus
        $this->pdo = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA journal_mode = WAL;'); // limite les verrous disque
        $this->pdo->exec('PRAGMA foreign_keys = ON;');

        if ($needsInit) {
            $this->createSchema();
        }
    }

    public function connection(): PDO
    {
        return $this->pdo;
    }

    private function createSchema(): void
    {
        $schema = <<<SQL
            CREATE TABLE IF NOT EXISTS businesses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                external_id TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                address TEXT NOT NULL,
                locality TEXT DEFAULT '',
                phone TEXT DEFAULT '',
                website TEXT DEFAULT '',
                photo_url TEXT DEFAULT '',
                latitude REAL,
                longitude REAL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        SQL;

        $this->pdo->exec($schema);
    }
}

