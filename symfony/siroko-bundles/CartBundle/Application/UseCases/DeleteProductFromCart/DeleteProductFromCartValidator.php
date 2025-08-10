<?php

namespace CartBundle\Application\UseCases\DeleteProductFromCart;

use Symfony\Component\Validator\Constraints as Assert;
use CartBundle\Infrastructure\Persistence\Mysql\DoctrineCartRepository as Repository;
use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validation;
use CartBundle\Domain\ValueObject\CartCode;
use ProductBundle\Domain\Repository\ProductRepository;
use App\Helper\ValidatorHelper;

class DeleteProductFromCartValidator
{
    public function __construct(
        private Repository $repository,
        private ProductRepository $productRepository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(DeleteProductFromCartCommand $command): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'cartCode' => $command->cartCode,
            'productId' => $command->productId,
        ], new Assert\Collection([
            'cartCode' => [
                new Assert\Required(),
                new Assert\NotNull(message: 'CartCode cannot be null.'),
                new Assert\NotBlank(message: 'CartCode cannot be blank.'),
                new Assert\Type(type: 'string', message: 'CartCode must be string.'),
                new Assert\Length(max: 36, maxMessage: 'CartCode cannot be longer than 36 characters.'),
            ],
            'productId' => [
                new Assert\Required(),
                new Assert\NotNull(message: 'ProductId cannot be null.'),
                new Assert\NotBlank(message: 'ProductId cannot be blank.'),
                new Assert\Type(type: 'integer', message: 'ProductId must be an integer.'),
                new Assert\GreaterThanOrEqual(value: 1, message: 'ProductId must be greater than or equal to 1.'),
            ],
        ]));

        $cart = $this->repository->findByCode(new CartCode($command->cartCode));
        if(!$cart)
        {
            $violations->add($this->validatorHelper->buildConstraintViolation('CartCode does not exists.', 'cartCode', $command->cartCode));

        }
        else{
            if($cart->getStatus() !== 'open')
            {
                $violations->add($this->validatorHelper->buildConstraintViolation('Cart is not open.', 'cartCode', $command->cartCode));
            }

            $productExists = false;
            foreach($cart->getItems() as $item)
            {
                if($item->getProductId()->value() === $command->productId)
                {
                    $productExists = true;
                }
            }
            if(!$productExists)
            {
                $violations->add($this->validatorHelper->buildConstraintViolation('Product does not exist in cart.', 'productId', $command->productId));
            }

            if($command->productId > 0)
            {
                $product = $this->productRepository->find($command->productId);
                if(!$product)
                {
                    $violations->add($this->validatorHelper->buildConstraintViolation('ProductId does not exist.', 'productId', $command->productId));
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
