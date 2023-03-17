<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use http\Exception\InvalidArgumentException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route(path: '/api')]
class jobController extends AbstractController
{
    use FormatJsonResponse;
    /**
     * @throws JsonException
     */
    #[Route(path: "/jobs", methods: ["POST"])]
    public function create(JobRepository $jobRepository,
                           CompanyRepository $companyRepository,
                           Request $request): Response
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $job = new Job();
        $job->setTitle($jsonParams['title']);
        $job->setDescription($jsonParams['description']);
        $job->setRequiredSkills($jsonParams['requiredSkills']);
        $job->setExperience($jsonParams['experience']);
        $company = $companyRepository->find($jsonParams['company']);

        if ($company === null){
            throw new InvalidArgumentException('Company not found');
        } else {
            $job->setCompany($company);
        }

        $jobRepository->save($job, true);

        return $this->JsonResponse('Job post created', $job->toArray(), 201);
    }


}