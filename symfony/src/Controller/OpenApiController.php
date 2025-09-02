<?php

namespace App\Controller;

use OpenApi\Generator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OpenApiController
{
    #[Route('/openapi.json', name: 'openapi_json')]
    public function openapi(): JsonResponse
    {
        $openapi = Generator::scan(
            [
                __DIR__ . '/../',
                __DIR__ . '/../../siroko-bundles/',
            ]
        );

        return new JsonResponse(json_decode($openapi->toJson()), 200, []);
    }
}
