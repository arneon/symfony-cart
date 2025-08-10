<?php

namespace CartBundle\Application\UseCases\CartView;


use Symfony\Component\Validator\Constraints as Assert;
use CartBundle\Infrastructure\Persistence\Mysql\DoctrineCartRepository as Repository;
use CartBundle\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validation;
use CartBundle\Domain\ValueObject\CartCode;
use App\Helper\ValidatorHelper;

class CartViewValidator
{
    public function __construct(
        private Repository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(string $cartCode): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'cartCode' => $cartCode,
        ], new Assert\Collection([
            'cartCode' => [
                new Assert\Required(),
                new Assert\NotNull(message: 'CartCode cannot be null.'),
                new Assert\NotBlank(message: 'CartCode cannot be blank.'),
                new Assert\Type(type: 'string', message: 'CartCode must be string.'),
                new Assert\Length(max: 36, maxMessage: 'CartCode cannot be longer than 36 characters.'),
            ],
        ]));

        $cart = $this->repository->findByCode(new CartCode($cartCode));
        if(!$cart)
        {
            $violations->add($this->validatorHelper->buildConstraintViolation('CartCode does not exist.', 'cartCode', $cartCode));
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
