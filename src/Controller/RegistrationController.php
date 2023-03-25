<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'auth')]
class RegistrationController extends AbstractController
{
    use JsonResponseFormat;

    /**
     * @throws \JsonException
     */
    #[Route(path: "/auth/signup", methods: ["POST"])]
    #[OA\Post(description: "Create User")]
    #[OA\RequestBody(
        description: "Json to create a User",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "email", type: "string", example: "test@email.com"),
                new OA\Property(property: "password", type: "string", example: "insert password")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the user email',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function register(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $user = new User();

        if (null === $jsonParams['password'] || '' === $jsonParams['password']) {
            return $this->json('Password not valid', 400);
        }

        $hashedPassword = $hasher->hashPassword($user, $jsonParams['password']);

        $user->setPassword($hashedPassword);
        $user->setEmail($jsonParams['email']);
        $user->setUsername($jsonParams['email']);

        $violations = $validator->validate($user);

        if (count($violations) === 0) {
            $userRepository->save($user, true);

            return $this->json((array)new ResponseDto('User created', [
                'email' => (string)$user->getEmail()
            ], 201));
        }

        $errorData = [];

        foreach ($violations as $violation) {
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse('Invalid inputs', $errorData, 400);
    }
}
