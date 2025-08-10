<?php

namespace CartBundle\Application\UseCases\AddProductToCart;

use Symfony\Component\Validator\Constraints as Assert;
use CartBundle\Infrastructure\Persistence\Mysql\DoctrineCartRepository as Repository;
use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validation;
use CartBundle\Domain\ValueObject\CartCode;
use ProductBundle\Domain\Repository\ProductRepository;
use App\Helper\ValidatorHelper;

class AddProductToCartValidator
{
    public function __construct(
        private Repository $repository,
        private ProductRepository $productRepository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(AddProductToCartCommand $command): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'customerId' => $command->customerId,
            'productId' => $command->productId,
            'qty' => $command->qty,
        ],
            new Assert\Collection([
                'fields' => [
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

                    'productId' => new Assert\Required([
                        new Assert\NotNull(['message' => 'ProductId cannot be null.']),
                        new Assert\NotBlank(['message' => 'ProductId cannot be blank.']),
                        new Assert\Type(['type' => 'integer', 'message' => 'ProductId must be an integer.']),
                        new Assert\GreaterThanOrEqual(['value' => 1, 'message' => 'ProductId must be greater than or equal to 1.']),
                    ]),

                    'qty' => new Assert\Required([
                        new Assert\NotBlank(['message' => 'Product quantity cannot be blank.']),
                        new Assert\Type(['type' => 'integer', 'message' => 'Product quantity must be an integer.']),
                        new Assert\GreaterThanOrEqual(['value' => 1, 'message' => 'Product quantity must be greater than or equal to 1.']),
                    ]),
                ],
            ])
        );

        if($command->productId > 0)
        {
            $product = $this->productRepository->find($command->productId);
            if(!$product)
            {
                $violations->add($this->validatorHelper->buildConstraintViolation('ProductId does not exist.', 'productId', $command->productId));
            }
            else
            {
                if($command->qty > $product->getStock()->value())
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Product quantity cannot be grater than the current stock quantity.', 'qty', $command->qty));
                }
            }
        }

        if($command->cartCode)
        {
            $cart = $this->repository->findByCode(new CartCode($command->cartCode));
            if(!$cart)
            {
                $violations->add($this->validatorHelper->buildConstraintViolation('CartCode does not exists.', 'cartCode', $command->cartCode));
            }
            else{
                if($cart->getStatus() !== 'open')
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Cart is not open.', 'cartStatus', $command->cartCode));
                }
                foreach($cart->getItems() as $item)
                {
                    if($item->getProductId()->value() == $command->productId)
                    {
                        $violations->add($this->validatorHelper->buildConstraintViolation('Product already exists in cart.', 'productId', $command->productId));
                    }
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
