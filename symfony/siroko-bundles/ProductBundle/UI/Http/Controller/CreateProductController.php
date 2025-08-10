<?php

namespace ProductBundle\UI\Http\Controller;

use ProductBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use ProductBundle\Application\UseCases\CreateProduct\CreateProductCommand;
use ProductBundle\Application\UseCases\CreateProduct\CreateProductHandler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;


class CreateProductController
{
    public function __construct(
        private CreateProductHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/products/',
        summary: 'Create Product',
        tags: ['Products'],
        requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
        type: 'object',
        required: ['name', 'price', 'stock'],
        properties: [
        new OA\Property(
        property: 'name',
        type: 'string',
        example: 'M2 Westfalia'
        ),
        new OA\Property(
        property: 'price',
        type: 'number',
        format: 'float',
        example: 80
        ),
        new OA\Property(
        property: 'stock',
        type: 'integer',
        example: 50
        )
        ]
        )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product Created'
            ),
            new OA\Response(
                response: 400,
                description: 'Product not created'
            )
        ]
    )]
    #[Route('', name: 'create_product', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new CreateProductCommand(
            ($data['name'] ?? ""),
            (float) ($data['price'] ?? 0.0),
            (int) ($data['stock'] ?? 0)
        );

        try {
            $createdId = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Product created', 'id' => $createdId], 201);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
