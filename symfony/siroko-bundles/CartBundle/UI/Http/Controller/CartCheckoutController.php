<?php

namespace CartBundle\UI\Http\Controller;

use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CartBundle\Application\UseCases\Checkout\CartCheckoutCommand;
use CartBundle\Application\UseCases\Checkout\CartCheckoutHandler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartCheckoutController
{
    public function __construct(
        private CartCheckoutHandler $handler,
    ) {}

    #[Route('carts/checkout', name: 'cart_checkout', methods: ['POST'])]
    #[IsGranted('LET_CART_WRITE')]
    #[OA\Post(
        path: '/api/carts/checkout',
        summary: 'Cart Checkout',
        tags: ['Carts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['cart_code', 'cart_total', 'customer_email', 'customer_id'],
                properties: [
                    new OA\Property(
                        property: 'cart_code',
                        type: 'string',
                        example: 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
                    ),
                    new OA\Property(
                        property: 'cart_total',
                        type: 'float',
                        example: 195.10
                    ),
                    new OA\Property(
                        property: 'customer_email',
                        type: 'string',
                        example: 'customer@gmail.com'
                    ),
                    new OA\Property(
                        property: 'customer_id',
                        type: 'integer',
                        example: 'Could be a null value'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cart checkout successful'
            ),
            new OA\Response(
                response: 400,
                description: 'Cart checkout failed'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true);

            $command = new CartCheckoutCommand(
                ($data['cart_code'] ?? null),
                ($data['cart_total'] ?? null),
                ($data['customer_email'] ?? null),
                ($data['customer_id'] ?? null),
            );
        }catch(\Exception $e)
        {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }

        try {
            $orderId = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Cart checkout successful', 'order_id' => $orderId], 201);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
