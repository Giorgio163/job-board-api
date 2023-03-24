<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api')]
#[OA\Tag(name: 'job post')]
class JobController extends AbstractController
{
    use JsonResponseFormat;

    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs", methods: ["POST"])]
    #[OA\Post(description: "Create company")]
    #[OA\RequestBody(
        description: "Json to create a job post",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Job title"),
                new OA\Property(property: "description", type: "string", example: "Job Description"),
                new OA\Property(property: "requiredSkills", type: "string", example: "Job Skills"),
                new OA\Property(property: "experience", type: "string", example: "Job experience"),
                new OA\Property(property: "company", type: "string", example: "Company Id to be added")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the Job ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function create(
        JobRepository $jobRepository,
        CompanyRepository $companyRepository,
        Request $request,
        ValidatorInterface $validator
    ): Response {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $job = new Job();
        $job->setTitle($jsonParams['title']);
        $job->setDescription($jsonParams['description']);
        $job->setRequiredSkills($jsonParams['requiredSkills']);
        $job->setExperience($jsonParams['experience']);
        $company = $companyRepository->find($jsonParams['company']);
        $job->setCompany($company);

        $violations = $validator->validate($job);

        if (count($violations)) {
            return $this->jsonResponse('Invalid input', $this->getViolationsFromList($violations), 400);
        }

        $jobRepository->save($job, true);

        return $this->json((array)new ResponseDto('Job post Created', [
            'id' => (string)$company->getId()
        ], 201));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return a job post by ID")]
    public function findById(?Job $job, SerializerInterface $serializer): JsonResponse
    {
        return $job->getCompany() === null
          ? $this->jsonResponse('Job post not found', [], 404)
          : $this->jsonResponse('Job post found', $serializer->serialize(
              $job,
              'json',
              [AbstractNormalizer::IGNORED_ATTRIBUTES =>  ['jobPosts', '__isCloning']]
          ));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs-posts", methods: ["GET"])]
    #[OA\Get(description: "Return all the job posts with optional filters")]
    #[OA\QueryParameter(name: "title", example: "jobTitle")]
    #[OA\QueryParameter(name: "companyName", example: "companyName")]
    #[OA\QueryParameter(name: "location", example: "location")]
    #[OA\Response(
        response: 200,
        description: "List of job posts response",
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function findAll(
        EntityManagerInterface $entityManager,
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse {
        $title = $request->get('title');
        $companyName = $request->get('companyName');
        $location = $request->get('location');

        $queryBuilder = $entityManager
            ->getRepository(Job::class)
            ->createQueryBuilder('j')
            ->leftJoin('j.company', 'c', Join::ON);

        if ($title !== null) {
            $queryBuilder->andWhere('j.title LIKE :title')
                ->setParameter(':title', "%$title%");
        }

        if ($companyName !== null) {
            $queryBuilder->andWhere('c.name LIKE :companyName')
                ->setParameter(':companyName', "%$companyName%");
        }

        if ($location !== null) {
            $queryBuilder->andWhere('c.location LIKE :location')
                ->setParameter(':location', "%$location%");
        }

        $queryBuilder->orderBy('j.title', 'ASC');

        $jobPosts = $queryBuilder->getQuery()->execute();

        return $this->jsonResponse(
            'List of job posts',
            $serializer->serialize($jobPosts, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['jobPosts',
                    '__isCloning', 'applicants']])
        );
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs/{id}", methods: ["PUT"])]
    #[OA\Put(description: "Update a job post by ID")]
    #[OA\RequestBody(
        description: "Json to Update a job post",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Job title"),
                new OA\Property(property: "description", type: "string", example: "Job Description"),
                new OA\Property(property: "requiredSkills", type: "string", example: "Job Skills"),
                new OA\Property(property: "experience", type: "string", example: "Job experience"),
             ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the job post ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function update(
        JobRepository $jobRepository,
        Request $request,
        string $id,
        ValidatorInterface $validator
    ): Response {
        $jobPost = $jobRepository->find($id);

        if ($jobPost === null) {
            return $this->jsonResponse('job post not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $jobPost->setTitle($jsonParams['title']);
        $jobPost->setDescription($jsonParams['description']);
        $jobPost->setRequiredSkills($jsonParams['requiredSkills']);
        $jobPost->setExperience($jsonParams['experience']);

        $violations = $validator->validate($jobPost);


        if (count($violations)) {
            return $this->jsonResponse('Invalid input', $this->getViolationsFromList($violations), 400);
        }

        $jobRepository->save($jobPost, true);

        return $this->json((array)new ResponseDto('Company updated', [
            'id' => (string)$jobPost->getId()
        ], 201));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete a job post by ID")]
    public function delete(JobRepository $jobRepository, string $id): Response
    {
        $jobPost = $jobRepository->find($id);

        if ($jobPost === null) {
            return $this->jsonResponse('job post not found', ['id' => $id], 404);
        }

        $jobRepository->remove($jobPost, true);

        return $this->jsonResponse('job post deleted', []);
    }
}
