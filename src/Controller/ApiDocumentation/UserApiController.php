<?php

namespace App\Controller\ApiDocumentation;
use App\Controller\ResponseDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api')]
#[OA\Tag(name: 'auth')]
class UserApiController
{
    #[Route(path: "/login_check", methods: ["POST"])]
    #[OA\Post(description: "User Login")]
    #[OA\RequestBody(
        description: "Payload to authenticate a User",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "test@email.com"),
                new OA\Property(property: "password", type: "string", example: "insert password")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Return the token',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function login(): void {}
}