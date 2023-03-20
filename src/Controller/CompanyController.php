<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use JsonException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api')]
#[OA\Tag(name: 'company')]
class CompanyController extends AbstractController
{
    use FormatJsonResponse;
    /**
     * @throws JsonException
     */
    #[Route(path: "/companies", methods: ["POST"])]
    #[OA\Post(description: "Create company")]
    #[OA\RequestBody(
        description: "Json to create a company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Company Name"),
                new OA\Property(property: "description", type: "string", example: "Company Description"),
                new OA\Property(property: "location", type: "string", example: "Company location"),
                new OA\Property(property: "contactInformation", type: "string", example: "Company contact information")
                ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the company ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function create(
        CompanyRepository $repository,
        Request $request,
        ValidatorInterface $validator
    ): Response
    {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = new Company();
        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactInformation($jsonParams['contactInformation']);

        $violations = $validator->validate($company);

        if (count($violations)) {
            $errorData = $this->getViolationsFromList($violations);
            return $this->JsonResponse('Invalid input', $errorData, 400);
        }

        $repository->save($company, true);

        return $this->json((array)new ResponseDto('Company created', [
            'id' => (string)$company->getId()
        ], 201));
    }

    #[Route(path: "/companies", methods: ["GET"])]
    #[OA\Get(description: "Return all the companies")]
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
    #[OA\Get(description: "Return a company by ID")]
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
    #[OA\Put(description: "Update company by ID")]
    #[OA\RequestBody(
        description: "Json to Update a company",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Company Update Name"),
                new OA\Property(property: "description", type: "string", example: "Company Update Description"),
                new OA\Property(property: "location", type: "string", example: "Company Update location"),
                new OA\Property(property: "contactInformation", type: "string",
                    example: "Company Update contact information")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Return the company ID',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(ref: new Model(type: ResponseDto::class))
    )]
    public function update(
        CompanyRepository $repository,
        Request $request,
        string $id,
        ValidatorInterface $validator): Response
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

        $violations = $validator->validate($company);

        if (count($violations) ===0) {
            if (count($violations)) {
                $errorData = $this->getViolationsFromList($violations);
                return $this->JsonResponse('Invalid input', $errorData, 400);
            }
        }
        $repository->save($company, true);

        return $this->json((array)new ResponseDto('Company updated', [
            'id' => (string)$company->getId()
        ], 201));
    }
// lesson 4 1h 40m
    #[Route(path: "/companies/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete a company by ID")]
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