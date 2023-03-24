<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait JsonResponseFormat
{
    private function getViolationsFromList($violations): array
    {
        $errorData = [];

        foreach ($violations as $violation) {
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errorData;
    }

    /**
     * @throws \JsonException
     */
    private function jsonResponse(string $message, mixed $data, int $statusCode = 200): JsonResponse
    {
        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }
        return new JsonResponse((array)new ResponseDto($message, $data, $statusCode), $statusCode);
    }
}
