<?php
declare(strict_types=1);

$storageDir = __DIR__ . '/storage';
if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
    throw new RuntimeException(sprintf('Impossible de créer le dossier storage : %s', $storageDir));
}

return [
    'db_path' => $storageDir . '/businesses.sqlite',
    'api_key' => getenv('GEOAPIFY_API_KEY') ?: '',
    'api_base_url' => '',
    'http_timeout' => 25,
    'per_page' => 10,
    'max_results' => 20,
];

