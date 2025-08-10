<?php

namespace CartBundle\UI\Http\Controller;

use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CartBundle\Application\UseCases\AddProductToCart\AddProductToCartCommand;
use CartBundle\Application\UseCases\AddProductToCart\AddProductToCartHandler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;


class AddProductToCartController
{
    public function __construct(
        private AddProductToCartHandler $handler,
    ) {}

    #[OA\Post(
        path: '/api/carts/',
        summary: 'Add Product to Cart',
        tags: ['Carts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['cart_code', 'customer_id', 'product_id', 'qty'],
                properties: [
                    new OA\Property(
                        property: 'cart_code',
                        type: 'string',
                        example: 'First time value should be null, then, it should be a string like this -> f47ac10b-58cc-4372-a567-0e02b2c3d479'
                    ),
                    new OA\Property(
                        property: 'customer_id',
                        type: 'integer',
                        example: 'Could be a null value'
                    ),
                    new OA\Property(
                        property: 'product_id',
                        type: 'integer',
                        example: 'Product ID should be an integer that exists in the product table'
                    ),
                    new OA\Property(
                        property: 'qty',
                        type: 'integer',
                        example: 'Quantity should be an integer that is greater than 0'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product Added to Cart'
            ),
            new OA\Response(
                response: 400,
                description: 'Product not added to Cart'
            )
        ]
    )]
    #[Route('', name: 'add_product_to_cart', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true);

            $command = new AddProductToCartCommand(
                ($data['cart_code'] ?? null),
                ($data['customer_id'] ?? null),
                ((int) $data['product_id'] ?? 0),
                ((int) $data['qty'] ?? 0)
            );
        }catch(\Exception $e)
        {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }

        try {
            $cartCode = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Product Added to Cart', 'cart_code' => $cartCode], 201);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
