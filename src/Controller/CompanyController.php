<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api')]
class CompanyController extends AbstractController
{
    use FormatJsonResponse;
    /**
     * @throws JsonException
     */
    #[Route(path: "/companies", methods: ["POST"])]
    public function create(CompanyRepository $repository, Request $request): Response
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = new Company();
        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactInformation($jsonParams['contactInformation']);

        $repository->save($company, true);

        return $this->JsonResponse('Company created', [
            'id' => (string)$company->getId()
        ], 201);
    }

    #[Route(path: "/companies", methods: ["GET"])]
    public function findAll(CompanyRepository $repository): Response
    {
        $companies = $repository->findAll();

        $response = [];
        foreach ($companies as $company){
            $response[] = $company->toArray();
        }

        return $this->JsonResponse('List of companies:', $response);
    }
    #[Route(path: "/companies/{id}", methods: ["GET"])]
    public function findById(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find($id);

        if ($company === null){
            return $this->JsonResponse('Company not found', ['id' => $id], 404);
        }

        return $this->JsonResponse('Company by ID:', $company->toArray());
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/companies/{id}", methods: ["PUT"])]
    public function update(CompanyRepository $repository,Request $request, string $id): Response
    {
        $company = $repository->find($id);

        if ($company === null){
            return $this->JsonResponse('Company not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactInformation($jsonParams['contactInformation']);

        $repository->save($company, true);

        return $this->JsonResponse('Company Updated', $company->toArray());

    }

    #[Route(path: "/companies/{id}", methods: ["DELETE"])]
    public function delete(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find($id);

        if ($company === null){
            return $this->JsonResponse('Company not found', ['id' => $id], 404);
        }

        $repository->remove($company, true);

        return $this->JsonResponse('Company deleted');
    }
}
