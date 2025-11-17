<?php
declare(strict_types=1);

final class JsonResponse
{
    public static function success(array $data, int $status = 200): void
    {
        self::send($data, $status);
    }

    public static function error(string $message, int $status = 400, array $context = []): void
    {
        self::send(['error' => $message, 'context' => $context], $status);
    }

    private static function send(array $payload, int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type');
        echo json_encode($payload, JSON_THROW_ON_ERROR);
    }
}

