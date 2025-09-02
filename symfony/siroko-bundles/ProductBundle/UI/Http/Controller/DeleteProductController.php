<?php

namespace ProductBundle\UI\Http\Controller;

use ProductBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use ProductBundle\Application\UseCases\DeleteProduct\DeleteProductHandler;
use ProductBundle\Application\UseCases\DeleteProduct\DeleteProductCommand;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteProductController
{
    public function __construct(private DeleteProductHandler $handler)
    {
    }


    #[Route('products/{id}', name: 'delete_product', methods: ['DELETE'])]
    #[IsGranted('LET_PRODUCT_WRITE')]
    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Delete Product',
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
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product Deleted'
            ),
        ]
    )]
    public function __invoke(mixed $id, Request $request): JsonResponse
    {
        $command = new DeleteProductCommand($id);

        try {
            $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Product deleted'], 200);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
