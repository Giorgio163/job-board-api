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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/v1')]
#[OA\Tag(name: 'company')]
class CompanyController extends AbstractController
{
    use JsonResponseFormat;

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
    ): Response {
        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company = new Company();
        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactInformation($jsonParams['contactInformation']);

        $violations = $validator->validate($company);

        if (count($violations)) {
            return $this->jsonResponse(
                'Invalid input',
                $this->getViolationsFromList($violations),
                400
            );
        }

        $repository->save($company, true);

        $data = [ 'id' => (string)$company->getId() ];
        return $this->jsonResponse('Company created', $data, 201);
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/companies", methods: ["GET"])]
    #[OA\Get(description: "Return all the companies")]
    public function findAll(CompanyRepository $repository, SerializerInterface $serializer): Response
    {
        $companies = $repository->findAll();

        $json = $serializer->serialize(
            $companies,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['company', '__isCloning', 'jobsApplied']]
        );

        return $this->jsonResponse('List of companies:', $json);
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/companies/{id}", methods: ["GET"])]
    #[OA\Get(description: "Return a company by ID")]
    public function findById(CompanyRepository $repository, string $id, SerializerInterface $serializer): Response
    {
        $company = $repository->find($id);

        if ($company === null) {
            return $this->jsonResponse('Company not found', ['id' => $id], 404);
        }

        $json = $serializer->serialize($company, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['company',
            '__isCloning',  'jobsApplied']]);

        return $this->jsonResponse('List of companies:', $json);
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
                new OA\Property(
                    property: "contactInformation",
                    type: "string",
                    example: "Company Update contact information"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
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
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): Response {
        $company = $repository->find($id);

        if ($company === null) {
            return $this->jsonResponse('Company not found', ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $company->setName($jsonParams['name']);
        $company->setDescription($jsonParams['description']);
        $company->setLocation($jsonParams['location']);
        $company->setContactInformation($jsonParams['contactInformation']);

        $violations = $validator->validate($company);


        if (count($violations)) {
            return $this->jsonResponse(
                'Invalid input',
                $this->getViolationsFromList($violations),
                400
            );
        }

        $repository->save($company, true);

        return $this->jsonResponse('Company updated', $serializer->serialize(
            $company,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['company', 'jobsApplied']]
        ));
    }

    /**
     * @throws JsonException
     */
    #[Route(path: "/companies/{id}", methods: ["DELETE"])]
    #[OA\Delete(description: "Delete a company by ID")]
    public function delete(CompanyRepository $repository, string $id): Response
    {
        $company = $repository->find($id);

        if ($company === null) {
            return $this->jsonResponse('Company not found', ['id' => $id], 404);
        }

        $repository->remove($company, true);

        return $this->jsonResponse('Company deleted', []);
    }
}
