<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/BusinessRepository.php';
require_once __DIR__ . '/../../src/GeoapifyClient.php';
require_once __DIR__ . '/../../src/JsonResponse.php';

$config = require __DIR__ . '/../../bootstrap.php';

// Répond rapidement aux pré-vols CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    exit;
}

// L’API est volontairement limitée aux requêtes GET
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    JsonResponse::error('Méthode non autorisée.', 405);
    exit;
}

// Validation minimale des paramètres côté serveur
$keyword = trim((string) ($_GET['keyword'] ?? ''));
$location = trim((string) ($_GET['location'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));

if ($keyword === '' || $location === '') {
    JsonResponse::error('Les paramètres keyword et location sont requis.', 422);
    exit;
}

try {
    // Initialisation simple des dépendances pour chaque appel
    $database = new Database($config['db_path']);
    $repository = new BusinessRepository($database);
    $client = new GeoapifyClient(
        $config['api_key'],
        (int) $config['http_timeout']
    );

    // Interroge Geoapify puis sauvegarde/actualise les commerces
    $businesses = $client->search($keyword, $location, (int) $config['max_results']);
    $repository->saveMany($businesses);

    // Pagination côté API pour ne renvoyer que 10 éléments
    $perPage = (int) $config['per_page'];
    $total = count($businesses);
    $pages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $pages);
    $offset = ($page - 1) * $perPage;
    $slice = array_slice($businesses, $offset, $perPage);

    JsonResponse::success([
        'results' => $slice,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'pages' => $pages,
        ],
    ]);
} catch (Throwable $exception) {
    JsonResponse::error(
        'Une erreur interne est survenue.',
        500,
        ['details' => $exception->getMessage()]
    );
}

