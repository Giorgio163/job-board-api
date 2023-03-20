<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait FormatJsonResponse
{
    private function getViolationsFromList($violations): array
    {
        $errorData = [];

        foreach ($violations as $violation) {
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errorData;
    }

    private function JsonResponse(string $message, array $data = [], int $statusCode = 200): JsonResponse
    {
        return new JsonResponse((array)new ResponseDto($message, $data, $statusCode), $statusCode);
    }
}
