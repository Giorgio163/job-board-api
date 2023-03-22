<?php

namespace App\Controller;
use App\Entity\Applicant;
use App\Entity\User;
use App\Repository\ApplicantRepository;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/api')]
#[OA\Tag(name: 'applicant')]
class ApplicantController extends AbstractController
{
    use FormatJsonResponse;

    /**
     * @throws JsonException
     */
    #[Route(path: "/applicants", methods: ["POST"])]
    #[OA\Post(description: "Create applicant")]
    #[OA\RequestBody(
        description: "Json to create an applicant",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Applicant Name"),
                new OA\Property(property: "contactInformation", type: "string",
                    example: "Applicant contact information"),
                new OA\Property(property: "jobPreferences", type: "string", example: "Applicant preferences")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the Applicant ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function create(
        ApplicantRepository $applicantRepository,
        Request $request,
        ValidatorInterface $validator
    ): Response
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $applicant = new Applicant();
        $applicant->setName($jsonParams['name']);
        $applicant->setContactInformation($jsonParams['contactInformation']);
        $applicant->setJobPreferences($jsonParams['jobPreferences']);

        $violations = $validator->validate($applicant);

        if (count($violations)) {
            $errorData = $this->getViolationsFromList($violations);
            return $this->JsonResponse('Invalid inputs', $errorData, 400);
        }

        $applicantRepository->save($applicant, true);

        $data = [ 'id' => (string)$applicant->getId() ];
        return $this->JsonResponse('Applicant created', $data, 201);
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/applicants", methods: ["GET"])]
    #[OA\Get(description: "Return all the applicants.")]
    public function findAll(
        ApplicantRepository $applicantRepository,
        TokenStorageInterface $storage,
        SerializerInterface $serializer
    ): Response {
        $token = $storage->getToken();
        $user = $token?->getUser();

        if (! $user instanceof User) {
            throw new \RuntimeException('Invalid user from token');
        }

        $applicant = $applicantRepository->findAll();

        return $this->JsonResponse(
            'List of applicants requested by ' . $user->getEmail(),
                    $serializer->serialize($applicant, 'json')
        );
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/applicants/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return an Applicant by ID")]
    public function findById(
        ApplicantRepository $applicantRepository,
        string $id,
        SerializerInterface $serializer
    ): Response {
        $applicant = $applicantRepository->find($id);

        if ($applicant === null) {
            return $this->JsonResponse('Applicant not found', ['id' => $id], 404);
        }

        return $this->JsonResponse('Applicant by ID', $serializer->serialize($applicant, 'json'));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/applicants/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Update applicant")]
    #[OA\RequestBody(
        description: "Json to update an applicant",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Applicant updated Name"),
                new OA\Property(property: "contactInformation", type: "string",
                    example: "Applicant updated contact information"),
                new OA\Property(property: "jobPreferences", type: "string", example: "Applicant updated preferences")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the Applicant ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function update(
        ApplicantRepository $applicantRepository,
        Request $request,
        string $id,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $applicant = $applicantRepository->find($id);

        if ($applicant === null) {
            return $this->JsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $applicant->setName($jsonParams['name']);
        $applicant->setContactInformation($jsonParams['contactInformation']);
        $applicant->setJobPreferences($jsonParams['jobPreferences']);

        $violations = $validator->validate($applicant);

        if (count($violations)) {
            $errorData = $this->getViolationsFromList($violations);
            return $this->JsonResponse('Invalid inputs', $errorData, 400);
        }

        $applicantRepository->save($applicant, true);
         return $this->JsonResponse('Applicant updated', $serializer->serialize($applicant, 'json'));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/applicants/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete an applicant by ID")]
    public function delete(ApplicantRepository $applicantRepository, string $id): Response
    {
        $applicant = $applicantRepository->find($id);

        if ($applicant === null) {
            return $this->JsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $applicantRepository->remove($applicant, true);

        return $this->JsonResponse('Applicant removed', []);
    }
}
