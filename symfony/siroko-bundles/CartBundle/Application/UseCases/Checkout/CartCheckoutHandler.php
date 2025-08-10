<?php

namespace CartBundle\Application\UseCases\Checkout;

use CartBundle\Domain\Repository\CartRepository;
use CartBundle\Domain\Repository\OrderRepository;
use CartBundle\Domain\Model\Order;
use CartBundle\Domain\ValueObject\CartCode;
use Doctrine\ORM\EntityManagerInterface;
use CartBundle\Infrastructure\Event\DomainEventDispatcher;

class CartCheckoutHandler {

    public function __construct(
        private CartRepository $repository,
        private OrderRepository $orderRepository,
        private DomainEventDispatcher $eventDispatcher,
        private CartCheckoutValidator $validator,
        private readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(CartCheckoutCommand $command): int
    {
        $this->validator->validate($command);

        $cartCode = new CartCode($command->cartCode);
        $cart = $this->repository->findByCode($cartCode);

        try {
            $this->em->beginTransaction();

            $order = new Order(
                $command->cartCode,
                (int) $command->customerId,
                $command->customerEmail,
                $cart->getItems(),
                round($cart->getTotal(), 2)
            );
            $orderId = $this->orderRepository->save($order);
            $order->setId($orderId);

            $cart->markAsCheckedOut($orderId);
            $this->repository->save($cart);

            $this->em->commit();

        }catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $events = array_merge(
            $order->pullDomainEvents(),
            $cart->pullDomainEvents()
        );

        $this->eventDispatcher->dispatchAll($events);

        return $orderId;
    }
}
