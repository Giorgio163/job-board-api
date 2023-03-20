<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api')]
#[OA\Tag(name: 'job')]
class jobController extends AbstractController
{
    use FormatJsonResponse;
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
    public function create(JobRepository $jobRepository,
                           CompanyRepository $companyRepository,
                           Request $request,
                           ValidatorInterface $validator): Response
    {
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
            $errorData = $this->getViolationsFromList($violations);
            return $this->JsonResponse('Invalid input', $errorData, 400);
        }

        $jobRepository->save($job, true);

        return $this->JsonResponse('Job post created', $job->toArray(), 201);
    }
}