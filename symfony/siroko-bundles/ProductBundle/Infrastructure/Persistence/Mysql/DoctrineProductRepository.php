<?php

namespace ProductBundle\Infrastructure\Persistence\Mysql;

use Doctrine\ORM\EntityManagerInterface;
use ProductBundle\Domain\Model\Product;
use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Domain\ValueObject\ProductName;
use ProductBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\ValueObject\ProductStock;

class DoctrineProductRepository implements ProductRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function save(Product $product): int
    {
        $entity = new ProductDoctrineEntity();
        $entity->setName($product->getName()->value());
        $entity->setPrice($product->getPrice()->value());
        $entity->setStock($product->getStock()->value());

        $this->em->persist($entity);
        $this->em->flush();

        return $entity->getId();
    }

    public function update(Product $product): int
    {
        $entity = $this->em->getRepository(ProductDoctrineEntity::class)->find($product->getId());

        if (!$entity) {
            throw new \Exception($product->getId());
        }

        $entity->setName($product->getName()->value());
        $entity->setPrice($product->getPrice()->value());
        $entity->setStock($product->getStock()->value());
        $this->em->flush();

        return $product->getId();
    }

    public function delete(Product $product): bool
    {
        $entity = $this->em->getRepository(ProductDoctrineEntity::class)->find($product->getId());

        if (!$entity) {
            throw new \Exception($product->getId());
        }

        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }

    public function find(int $id): ?Product
    {
        $entity = $this->em->getRepository(ProductDoctrineEntity::class)->find($id);

        if (!$entity) {
            return null;
        }

        $product = new Product(
            new ProductName($entity->getName()),
            new ProductPrice($entity->getPrice()),
            new ProductStock($entity->getStock()),
        );

        $product->setId($entity->getId());
        return $product;
    }

    public function existsByName(string $name, $exceptId=null): bool
    {
        $query = $this->em->createQueryBuilder();

        $query->select('p')
            ->from(ProductDoctrineEntity::class, 'p')
            ->where('p.name = :name')
            ->setParameter('name', $name);

        if ($exceptId !== null) {
            $query->andWhere('p.id != :exceptId')
                ->setParameter('exceptId', $exceptId);
        }

        return $query->getQuery()->getOneOrNullResult() !== null;
    }
}
