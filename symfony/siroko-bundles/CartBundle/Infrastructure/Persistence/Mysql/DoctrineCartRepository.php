<?php

namespace CartBundle\Infrastructure\Persistence\Mysql;

use CartBundle\Domain\Model\Cart as DomainCart;
use CartBundle\Domain\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Infrastructure\Persistence\Mysql\Mapper\CartMapper;


class DoctrineCartRepository implements CartRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartMapper $mapper,
    ) {}

    public function save(DomainCart $cart): int
    {
        $existingEntity = $this->em->getRepository(CartDoctrineEntity::class)
            ->findOneBy(['cartCode' => $cart->getCode()->value()]);

        $entity = $this->mapper->toDoctrine($cart, $existingEntity);

        if (!$existingEntity) {
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $entity->getId();
    }

    public function find(?CartId $id): ?DomainCart
    {
        if (!$id) {
            return null;
        }

        $entity = $this->em->find(CartDoctrineEntity::class, $id->value());

        if (!$entity) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    public function findByCode(CartCode $code): ?DomainCart
    {
        if (!$code->value()) {
            return null;
        }

        $entity = $this->em->getRepository(CartDoctrineEntity::class)
            ->findOneBy(['cartCode' => $code->value()]);

        if (!$entity) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    public function findOpenCartsWithProduct0(ProductId $productId): array
    {
        $entities = $this->em->getRepository(CartDoctrineEntity::class)
            ->findBy(['status' => 'open', 'productId' => $productId->value()]);

        return array_map(fn($entity) => $this->mapper->toDomain($entity), $entities);
    }

    public function delete(CartCode $cartCode): bool
    {
        $entity = $this->em->getRepository(CartDoctrineEntity::class)
            ->findOneBy(['cartCode' => $cartCode->value()]);

        if ($entity !== null) {
            $this->em->remove($entity);
            $this->em->flush();

            return true;
        }

        return false;
    }
    public function findOpenCartsWithProduct(ProductId $productId): array
    {
        $query = $this->em->createQueryBuilder();

        $query->select('c')
            ->from(CartDoctrineEntity::class, 'c')
            ->join('c.items', 'i')
            ->where('c.status = :status')
            ->andWhere('i.productId = :productId')
            ->setParameter('status', 'open')
            ->setParameter('productId', (int) $productId->value());
        $results = $query->getQuery()->getResult();

        return array_map(fn($entity) => $this->mapper->toDomain($entity), $results);
    }

}
