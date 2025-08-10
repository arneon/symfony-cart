<?php

namespace ProductBundle\UI\Http\Controller;

use ProductBundle\Application\UseCases\FindAllProduct\FindAllProductHandler;
use ProductBundle\Application\UseCases\FindAllProduct\FindAllProductQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;

class ProductApiController extends AbstractController
{
    public function __construct(
        private FindAllProductHandler $productQuery
    )
    {
    }

    #[OA\Get(
        tags: ['Products'],
        path: '/api/products',
        summary: 'Product List',
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
    #[Route('', name: 'product_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $products = $this->productQuery->__invoke(new FindAllProductQuery());
        return $this->json($products);
    }
}
