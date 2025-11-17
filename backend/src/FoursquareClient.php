<?php
declare(strict_types=1);

final class FoursquareClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout = 10
    ) {
        if ($this->apiKey === '') {
            throw new InvalidArgumentException('FOURSQUARE_API_KEY est manquant.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $keyword, string $location, int $limit = 50): array
    {
        $query = http_build_query([
            'query' => $keyword,
            'near' => $location,
            'limit' => $limit,
            'sort' => 'RELEVANCE',
            'fields' => 'fsq_id,name,location,geocodes,tel,website,photos',
        ]);

        $response = $this->request(sprintf('%s?%s', $this->baseUrl, $query));
        $payload = json_decode($response, true, flags: JSON_THROW_ON_ERROR);

        if (!isset($payload['results']) || !is_array($payload['results'])) {
            return [];
        }

        $normalized = [];
        foreach ($payload['results'] as $place) {
            $business = $this->normalizePlace($place);
            if ($business !== null) {
                $normalized[] = $business;
            }
        }

        return $normalized;
    }

    private function request(string $url): string
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('Impossible d’initialiser cURL.');
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $response = curl_exec($handle);
        $error = curl_error($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($response === false) {
            throw new RuntimeException('Requête Foursquare échouée : ' . $error);
        }

        if ($status >= 400) {
            throw new RuntimeException(sprintf('Foursquare a renvoyé le statut %d', $status));
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $place
     */
    private function normalizePlace(array $place): ?array
    {
        $id = $place['fsq_id'] ?? null;
        $name = $place['name'] ?? null;
        $location = $place['location'] ?? [];
        $address = $location['formatted_address'] ?? null;

        if ($id === null || $name === null || $address === null) {
            return null;
        }

        $locality = trim(($location['locality'] ?? '') . ' ' . ($location['country'] ?? ''));
        $geocodes = $place['geocodes']['main'] ?? [];

        return [
            'id' => (string) $id,
            'name' => (string) $name,
            'address' => (string) $address,
            'locality' => $locality,
            'phone' => $place['tel'] ?? '',
            'website' => $place['website'] ?? '',
            'photo' => $this->extractPhotoUrl($place['photos'] ?? []),
            'latitude' => $geocodes['latitude'] ?? null,
            'longitude' => $geocodes['longitude'] ?? null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $photos
     */
    private function extractPhotoUrl(array $photos): string
    {
        $photo = $photos[0] ?? null;
        if ($photo === null || !isset($photo['prefix'], $photo['suffix'])) {
            return '';
        }

        return sprintf('%s500x500%s', $photo['prefix'], $photo['suffix']);
    }
}

