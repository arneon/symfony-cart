<?php

namespace CartBundle\Application\UseCases\Checkout;

use Symfony\Component\Validator\Constraints as Assert;
use CartBundle\Infrastructure\Persistence\Mysql\DoctrineCartRepository as Repository;
use CartBundle\Infrastructure\Persistence\Mysql\DoctrineOrderRepository as OrderRepository;
use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validation;
use CartBundle\Domain\ValueObject\CartCode;
use App\Helper\ValidatorHelper;

class CartCheckoutValidator
{
    public function __construct(
        private Repository $repository,
        private OrderRepository $orderRepository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(CartCheckoutCommand $command): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'cartCode' => $command->cartCode,
            'cartTotal' => $command->cartTotal,
            'customerEmail' => $command->customerEmail,
            'customerId' => $command->customerId,
        ],
            new Assert\Collection([
                'fields' => [
                    'cartCode' => new Assert\Required([
                        new Assert\NotNull(['message' => 'cartCode cannot be null.']),
                        new Assert\NotBlank(['message' => 'cartCode cannot be blank.']),
                        new Assert\Type(['type' => 'string', 'message' => 'ProductId must be a string.']),
                    ]),
                    'cartTotal' => new Assert\Required([
                        new Assert\NotNull(['message' => 'cartTotal cannot be null.']),
                        new Assert\GreaterThanOrEqual(['value' => 1, 'message' => 'cartTotal must be >= 1.']),
                        new Assert\Type(['type' => 'numeric', 'message' => 'cartTotal must be a decimal number.']),
                    ]),
                    'customerEmail' => new Assert\Required([
                        new Assert\NotNull(['message' => 'customerEmail cannot be null.']),
                        new Assert\NotBlank(['message' => 'customerEmail cannot be blank.']),
                        new Assert\Email(['message' => 'customerEmail must be email type.']),
                    ]),
                    'customerId' => new Assert\Optional([
                        new Assert\AtLeastOneOf([
                            new Assert\IsNull(['message' => 'customerId could be null.']),
                            new Assert\Blank(['message' => 'customerId could be empty.']),
                            new Assert\Sequentially([
                                new Assert\Type(['type' => 'integer', 'message' => 'customerId must be an integer.']),
                                new Assert\GreaterThanOrEqual(['value' => 1, 'message' => 'customerId must be greater than or equal to 1.']),
                            ]),
                        ]),
                    ]),
                ],
            ])
        );

        if($command->cartCode)
        {
            $cart = $this->repository->findByCode(new CartCode($command->cartCode));
            if(!$cart)
            {
                $violations->add($this->validatorHelper->buildConstraintViolation('CartCode does not exists.', 'cartCode', $command->cartCode));
            }
            else{
                if($this->orderRepository->existsByCartCode($command->cartCode))
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Cart is already associated to an order.', 'cartStatus', $command->cartCode));
                }

                if ($cart->isEmpty()) {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Cart is empty.', 'cartStatus', $command->cartCode));
                }

                if($cart->getStatus() !== 'open')
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Cart is not open.', 'cartStatus', $command->cartCode));
                }

                if($cart->getTotal() !== (float)$command->cartTotal)
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Cart total does not match. Please verify', 'cartTotal', $command->cartTotal));
                }
            }
        }

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new ValidationException($errors);
        }
    }
}
