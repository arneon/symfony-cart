<?php

namespace ProductBundle\UI\Http\Controller;

use ProductBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ProductBundle\Application\UseCases\UpdateProduct\UpdateProductCommand;
use ProductBundle\Application\UseCases\UpdateProduct\UpdateProductHandler;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateProductController
{
    public function __construct(
        private UpdateProductHandler $handler
    )
    {
    }

    #[Route('products/{id}', name: 'update_product', methods: ['PUT'])]
    #[IsGranted('LET_PRODUCT_WRITE')]
    #[OA\Put(
        path: '/api/products/{id}',
        summary: 'Update Product',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Product ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
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
                response: 200,
                description: 'Product Updated'
            ),
            new OA\Response(
                response: 400,
                description: 'Product not updated'
            )
        ]
    )]
    public function __invoke(mixed $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new UpdateProductCommand(
            $id,
            $data['name'] ?? null,
            $data['price'] ?? null,
            $data['stock'] ?? null,
        );

        try {
            $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Product updated'], 200);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
