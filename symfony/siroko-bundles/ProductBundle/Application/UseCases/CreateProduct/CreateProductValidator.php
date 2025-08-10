<?php

namespace ProductBundle\Application\UseCases\CreateProduct;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository as Repository;
use ProductBundle\Domain\Exception\ValidationException;
use App\Helper\ValidatorHelper;

class CreateProductValidator
{
    public function __construct(
        private Repository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(CreateProductCommand $command): void
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'name' => $command->name,
            'price' => $command->price,
            'stock' => $command->stock,
        ], new Assert\Collection([
            'name' => [
                new Assert\NotBlank(message: 'Name cannot be blank.'),
                new Assert\Type(type: 'string', message: 'Name must be a string.'),
                new Assert\Length(max: 100, maxMessage: 'Name cannot be longer than 100 characters.'),
            ],
            'price' => [
                new Assert\NotBlank(message: 'Price cannot be blank.'),
                new Assert\Type(type: 'numeric', message: 'Price must be a number.'),
                new Assert\GreaterThanOrEqual(value: 0, message: 'Price must be greater than or equal to 0.'),
            ],
            'stock' => [
                new Assert\NotBlank(message: 'Stock cannot be blank.'),
                new Assert\Type(type: 'numeric', message: 'Stock must be a number.'),
                new Assert\GreaterThanOrEqual(value: 0, message: 'Stock must be greater than or equal to 0.'),
            ],
        ]));

        if($command->name)
        {
            if ($this->repository->existsByName($command->name)) {
                $violations->add($this->validatorHelper->buildConstraintViolation('Name already exists.', 'name', $command->name));
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
