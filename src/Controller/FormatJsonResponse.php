<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait FormatJsonResponse
{
    private function JsonResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}