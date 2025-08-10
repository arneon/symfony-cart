<?php

namespace CartBundle\Infrastructure\Persistence\Mysql;

use CartBundle\Domain\Model\Cart as DomainCart;
use CartBundle\Domain\Model\Order;
use CartBundle\Domain\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Infrastructure\Persistence\Mysql\Mapper\OrderMapper;


class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderMapper $mapper,
    ) {}


    public function save(Order $order): int
    {
        $entity = $this->mapper->toDoctrine($order, null);
        $this->em->persist($entity);
        $this->em->flush();

        return $entity->getId();
    }

    public function existsByCartCode(string $cartCode): bool
    {
        $entity = $this->em->getRepository(OrderDoctrineEntity::class)
            ->findOneBy(['cartCode' => $cartCode]);
        return $entity !== null;
    }
}
