<?php

namespace App\Controller;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Siroko Cart - Api Documentation',
    description: 'Api Documentation'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    in: 'header',
    name: 'Authorization'
)]
#[OA\OpenApi(security: [['bearerAuth' => []]])]
class ApiSpecController
{
}
