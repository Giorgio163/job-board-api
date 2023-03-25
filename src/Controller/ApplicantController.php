<?php

namespace App\Controller;

use App\Entity\Applicant;
use App\Entity\User;
use App\Repository\ApplicantRepository;
use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'applicant')]
class ApplicantController extends AbstractController
{
    use JsonResponseFormat;

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
                new OA\Property(
                    property: "contactInformation",
                    type: "string",
                    example: "Applicant contact information"
                ),
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
    ): Response {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $applicant = new Applicant();
        $applicant->setName($jsonParams['name']);
        $applicant->setContactInformation($jsonParams['contactInformation']);
        $applicant->setJobPreferences($jsonParams['jobPreferences']);

        $violations = $validator->validate($applicant);

        if (count($violations)) {
            $errorData = $this->getViolationsFromList($violations);
            return $this->jsonResponse('Invalid inputs', $errorData, 400);
        }

        $applicantRepository->save($applicant, true);

        $data = [ 'id' => (string)$applicant->getId() ];
        return $this->jsonResponse('Applicant created', $data, 201);
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

        return $this->jsonResponse(
            'List of applicants requested by ' . $user->getEmail(),
            $serializer->serialize(
                $applicant,
                'json',
                [AbstractNormalizer::IGNORED_ATTRIBUTES => ['__isCloning', 'applicants', 'jobPosts', 'company']]
            )
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
            return $this->jsonResponse('Applicant not found', ['id' => $id], 404);
        }

        return $this->jsonResponse('Applicant by ID', $serializer->serialize(
            $applicant,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['__isCloning', 'applicants', 'jobPosts']]
        ));
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
                new OA\Property(
                    property: "contactInformation",
                    type: "string",
                    example: "Applicant updated contact information"
                ),
                new OA\Property(property: "jobPreferences", type: "string", example: "Applicant updated preferences")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
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
            return $this->jsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $applicant->setName($jsonParams['name']);
        $applicant->setContactInformation($jsonParams['contactInformation']);
        $applicant->setJobPreferences($jsonParams['jobPreferences']);

        $violations = $validator->validate($applicant);

        if (count($violations)) {
            $errorData = $this->getViolationsFromList($violations);
            return $this->jsonResponse('Invalid inputs', $errorData, 400);
        }

        $applicantRepository->save($applicant, true);
         return $this->jsonResponse('Applicant updated', $serializer->serialize($applicant, 'json'));
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
            return $this->jsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $applicantRepository->remove($applicant, true);

        return $this->jsonResponse('Applicant removed', []);
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/job-applicants/apply/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Apply for a job")]
    #[OA\RequestBody(
        description: "Json to Apply for a job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "jobsApplied",
                    type: "string",
                    example: "ID of the job you want to apply for"
                ),
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
    public function apply(
        ApplicantRepository $applicantRepository,
        JobRepository $jobRepository,
        Request $request,
        string $id,
        SerializerInterface $serializer
    ): Response {
        $applicant = $applicantRepository->find($id);

        if ($applicant === null) {
            return $this->jsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $jobPost = $jobRepository->find($jsonParams['jobsApplied']);

        if ($jobPost === null) {
            return $this->jsonResponse('Job posts not found', ['id' => $id], 404);
        }

        $applicant->addJobsApplied($jobPost);

        if (null === $jsonParams['jobsApplied'] || '' === $jsonParams['jobsApplied']) {
            return $this->json('ID post not valid', 400);
        }

        $applicantRepository->save($applicant, true);
        return $this->jsonResponse('Application successful', $serializer->serialize(
            $applicant,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['__isCloning', 'applicants', 'company']]
        ));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/job-applicants/remove/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Remove application for a job")]
    #[OA\RequestBody(
        description: "Remove application for a job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "jobsApplied",
                    type: "string",
                    example: "ID of the job you want to remove"
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Return the Applicant ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function removeApplication(
        ApplicantRepository $applicantRepository,
        JobRepository $jobRepository,
        Request $request,
        string $id,
        SerializerInterface $serializer
    ): Response {

        $applicant = $applicantRepository->find($id);

        if ($applicant === null) {
            return $this->jsonResponse('Applicant not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $jobPost = $jobRepository->find($jsonParams['jobsApplied']);

        if ($jobPost === null) {
            return $this->jsonResponse('Job posts not found', ['id' => $id], 404);
        }

        $applicant->removeJobsApplied($jobPost);

        if (null === $jsonParams['jobsApplied'] || '' === $jsonParams['jobsApplied']) {
            return $this->json('ID post not valid', 400);
        }

        $applicantRepository->save($applicant, true);
        return $this->jsonResponse(
            'Application successful removed',
            $serializer->serialize(
                $applicant,
                'json',
                [AbstractNormalizer::IGNORED_ATTRIBUTES => ['__isCloning', 'applicants', 'company']]
            )
        );
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/job-applicants", methods: ["GET"])]
    #[OA\Get(description: "Return a list of applicants for a job posting
     and the list of jobs applied for by an applicant. With some filters")]
    #[OA\QueryParameter(name: "ApplicantId", example: "ID of the applicant")]
    #[OA\QueryParameter(name: "jobId", example: "ID of the job post")]
    #[OA\Response(
        response: 200,
        description: "List of job posts response",
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function find(
        EntityManagerInterface $entityManager,
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse {
        $ApplicantId = $request->get('ApplicantId');
        $jobPost = $request->get('jobId');

        $queryBuilder = $entityManager
            ->getRepository(Applicant::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.jobsApplied', 'j', Join::ON);

        if ($ApplicantId !== null) {
            $queryBuilder->andWhere('a.id LIKE :ApplicantId')
                ->setParameter(':ApplicantId', Uuid::fromString($ApplicantId)->toBinary());
        }

        if ($jobPost !== null) {
            $queryBuilder->andWhere('j.id LIKE :jobId')
                ->setParameter(':jobId', Uuid::fromString($jobPost)->toBinary());
        }

        $applications = $queryBuilder->getQuery()->execute();

        return $this->jsonResponse(
            'List of applications',
            $serializer->serialize($applications, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['company',
                    '__isCloning', 'applicants']])
        );
    }
}
