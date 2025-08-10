<?php

namespace CartBundle\UI\Http\Controller;

use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CartBundle\Application\UseCases\DeleteProductFromCart\DeleteProductFromCartCommand;
use CartBundle\Application\UseCases\DeleteProductFromCart\DeleteProductFromCartHandler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class DeleteProductFromCartController
{
    public function __construct(
        private DeleteProductFromCartHandler $handler,
    ) {}

    #[OA\Delete(
        path: '/api/carts/',
        summary: 'Delete Product from Cart',
        tags: ['Carts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['cart_code', 'product_id'],
                properties: [
                    new OA\Property(
                        property: 'cart_code',
                        type: 'string',
                        example: 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
                    ),
                    new OA\Property(
                        property: 'product_id',
                        type: 'integer',
                        example: 17
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product deleted from Cart'
            ),
            new OA\Response(
                response: 400,
                description: 'Product not deleted from Cart'
            )
        ]
    )]
    #[Route('', name: 'delete_product_from_cart', methods: ['DELETE'])]
    public function __invoke(Request $request): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true);

            $command = new DeleteProductFromCartCommand(
                ($data['cart_code'] ?? null),
                ($data['product_id'] ?? 0),
            );
        }catch(\Exception $e)
        {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }

        try {
            $cartCode = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Product Deleted from Cart', 'cart_code' => $cartCode], 200);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
