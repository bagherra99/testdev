<?php
declare(strict_types=1);

final class BusinessRepository
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->connection();
    }

    /**
     * @param array<int, array<string, mixed>> $businesses
     */
    public function saveMany(array $businesses): void
    {
        if ($businesses === []) {
            return;
        }

        // Upsert pour éviter les doublons tout en gardant les données fraîches
        $query = <<<SQL
            INSERT INTO businesses (
                external_id,
                name,
                address,
                locality,
                phone,
                website,
                photo_url,
                latitude,
                longitude,
                updated_at
            ) VALUES (
                :external_id,
                :name,
                :address,
                :locality,
                :phone,
                :website,
                :photo_url,
                :latitude,
                :longitude,
                CURRENT_TIMESTAMP
            )
            ON CONFLICT(external_id) DO UPDATE SET
                name = excluded.name,
                address = excluded.address,
                locality = excluded.locality,
                phone = excluded.phone,
                website = excluded.website,
                photo_url = excluded.photo_url,
                latitude = excluded.latitude,
                longitude = excluded.longitude,
                updated_at = CURRENT_TIMESTAMP;
        SQL;

        $statement = $this->pdo->prepare($query);

        foreach ($businesses as $business) {
            $statement->execute([
                ':external_id' => $business['id'],
                ':name' => $business['name'],
                ':address' => $business['address'],
                ':locality' => $business['locality'] ?? '',
                ':phone' => $business['phone'] ?? '',
                ':website' => $business['website'] ?? '',
                ':photo_url' => $business['photo'] ?? '',
                ':latitude' => $business['latitude'] ?? null,
                ':longitude' => $business['longitude'] ?? null,
            ]);
        }
    }
}

