<?php
declare(strict_types=1);

final class GeoapifyClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeout = 10
    ) {
        if ($this->apiKey === '') {
            throw new InvalidArgumentException('GEOAPIFY_API_KEY est manquant.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $keyword, string $location, int $limit = 20): array
    {
        // On géocode d’abord la localisation pour cibler la recherche
        [$lon, $lat] = $this->geocode($location);

        $query = http_build_query([
            'filter' => sprintf('circle:%F,%F,3000', $lon, $lat),
            'bias' => sprintf('proximity:%F,%F', $lon, $lat),
            'limit' => $limit,
            'categories' => 'commercial,catering,service',
            'name' => $keyword,
            'apiKey' => $this->apiKey,
        ]);

        $url = 'https://api.geoapify.com/v2/places?' . $query;
        $response = $this->request($url);
        $payload = json_decode($response, true, flags: JSON_THROW_ON_ERROR);

        if (!isset($payload['features']) || !is_array($payload['features'])) {
            return [];
        }

        $normalized = [];
        foreach ($payload['features'] as $feature) {
            $business = $this->normalizeFeature($feature);
            if ($business !== null) {
                $normalized[] = $business;
            }
        }

        return $normalized;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function geocode(string $location): array
    {
        // Géocodage ultra simple : un seul point suffit pour centrer la recherche
        $query = http_build_query([
            'text' => $location,
            'limit' => 1,
            'apiKey' => $this->apiKey,
        ]);

        $url = 'https://api.geoapify.com/v1/geocode/search?' . $query;
        $response = $this->request($url);
        $payload = json_decode($response, true, flags: JSON_THROW_ON_ERROR);

        if (
            !isset($payload['features'][0]['geometry']['coordinates']) ||
            !is_array($payload['features'][0]['geometry']['coordinates'])
        ) {
            throw new RuntimeException('Impossible de géocoder la localisation fournie.');
        }

        $coords = $payload['features'][0]['geometry']['coordinates'];

        return [(float) $coords[0], (float) $coords[1]];
    }

    // Enveloppe cURL pour centraliser les timeouts et les erreurs HTTP
    private function request(string $url): string
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Impossible d’initialiser cURL.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($handle);
        $error = curl_error($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($response === false) {
            throw new RuntimeException('Requête Geoapify échouée : ' . $error);
        }

        if ($status >= 400) {
            throw new RuntimeException(sprintf('Geoapify a renvoyé le statut %d', $status));
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $feature
     */
    // Transforme la réponse Geoapify dans un format unique pour le front
    private function normalizeFeature(array $feature): ?array
    {
        $properties = $feature['properties'] ?? [];
        $geometry = $feature['geometry'] ?? [];
        $coordinates = $geometry['coordinates'] ?? [];

        $id = $properties['place_id'] ?? $properties['datasource']['raw']['osm_id'] ?? null;
        $name = $properties['name'] ?? null;
        $address = $properties['formatted'] ?? null;

        if ($id === null || $name === null || $address === null) {
            return null;
        }

        $locality = trim(($properties['city'] ?? '') . ' ' . ($properties['country'] ?? ''));

        return [
            'id' => (string) $id,
            'name' => (string) $name,
            'address' => (string) $address,
            'locality' => $locality,
            'phone' => $properties['contact:phone'] ?? $properties['datasource']['raw']['phone'] ?? '',
            'website' => $properties['website'] ?? $properties['datasource']['raw']['website'] ?? '',
            'photo' => '',
            'latitude' => isset($coordinates[1]) ? (float) $coordinates[1] : null,
            'longitude' => isset($coordinates[0]) ? (float) $coordinates[0] : null,
        ];
    }
}


