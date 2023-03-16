<?php

namespace App\Controller;

use App\Repository\JobRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route(path: '/api')]
class jobController
{
    #[Route(path: "/jobs", methods: ["POST"])]
    public function create(JobRepository $repository, Request $request): Response
    {

    }
}