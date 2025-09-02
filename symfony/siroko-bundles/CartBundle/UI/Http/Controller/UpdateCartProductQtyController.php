<?php

namespace CartBundle\UI\Http\Controller;

use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use CartBundle\Application\UseCases\UpdateCartProductQty\UpdateCartProductQtyCommand;
use CartBundle\Application\UseCases\UpdateCartProductQty\UpdateCartProductQtyHandler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UpdateCartProductQtyController
{
    public function __construct(
        private UpdateCartProductQtyHandler $handler,
    ) {}

    #[Route('carts', name: 'update_cart_product_qty', methods: ['PUT'])]
    #[IsGranted('LET_CART_WRITE')]
    #[OA\Put(
        path: '/api/carts',
        summary: 'Update Cart Product Quantity',
        tags: ['Carts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['cart_code', 'product_id', 'qty'],
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
                    new OA\Property(
                        property: 'qty',
                        type: 'integer',
                        example: 7
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart Product Quantity Updated'
            ),
            new OA\Response(
                response: 400,
                description: 'Product Qty not updated in Cart'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true);

            $command = new UpdateCartProductQtyCommand(
                ($data['cart_code'] ?? null),
                ((int) $data['product_id'] ?? 0),
                ((int) $data['qty'] ?? 0)
            );
        }catch(\Exception $e)
        {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }

        try {
            $cartCode = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'Cart Product Quantity Updated', 'cart_code' => $cartCode], 200);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], 400);
        }
    }
}
