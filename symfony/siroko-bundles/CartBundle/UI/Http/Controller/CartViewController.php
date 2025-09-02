<?php

namespace CartBundle\UI\Http\Controller;

use CartBundle\Application\UseCases\CartView\CartViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CartViewController extends AbstractController
{
    public function __construct(
        private CartViewHandler $cartQuery
    )
    {
    }

    #[Route('carts/{cart_code}', name: 'cart_view', methods: ['GET'])]
    #[IsGranted('LET_CART_READ')]
    #[OA\Get(
        path: '/api/carts/{cart_code}',
        summary: 'View Cart',
        tags: ['Carts'],
        parameters: [
            new OA\Parameter(
                name: 'cart_code',
                in: 'path',
                required: true,
                description: 'Cart Code',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
    public function getCart(mixed $cart_code, Request $request): JsonResponse
    {
        try{
            $cart = $this->cartQuery->__invoke($cart_code);
            return $this->json($cart);
        }catch(\Exception $e)
        {
            return new JsonResponse(['errors' => $e->getMessage()], 400);
        }
    }
}
