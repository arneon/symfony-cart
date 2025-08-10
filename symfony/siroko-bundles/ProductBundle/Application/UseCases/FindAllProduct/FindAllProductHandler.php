<?php

namespace ProductBundle\Application\UseCases\FindAllProduct;

use ProductBundle\Domain\Repository\ProductReadRepository;

final readonly class FindAllProductHandler
{
    public function __construct(
        private ProductReadRepository $repository
    )
    {
    }

    public function __invoke(FindAllProductQuery $query): array
    {
        $products = $this->repository->findAll();

        return array_map(
            fn(array $product) => new ProductResponse(
                $product['id'],
                $product['name'],
                $product['price'],
                $product['stock'],
            ),
            $products
        );
    }
}
