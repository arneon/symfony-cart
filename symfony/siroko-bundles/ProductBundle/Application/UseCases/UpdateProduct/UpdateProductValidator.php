<?php

namespace ProductBundle\Application\UseCases\UpdateProduct;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Domain\Exception\ValidationException;
use App\Helper\ValidatorHelper;

class UpdateProductValidator
{
    public function __construct(
        private DoctrineProductRepository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(UpdateProductCommand $command): void
    {
        $id = filter_var($command->id, FILTER_VALIDATE_INT);

        $validator = Validation::createValidator();
        $violations = $validator->validate([
            'id' => $id,
            'name' => $command->name,
            'price' => $command->price,
            'stock' => $command->stock,
        ], new Assert\Collection([
            'id' => [
                new Assert\Type([
                    'type' => 'integer',
                    'message' => 'Id must be an integer.',
                ]),
                new Assert\Positive([
                    'message' => 'Id must be positive.',
                ]),
            ],
            'name' => [
                new Assert\NotBlank([
                    'message' => 'Name cannot be blank.',
                ]),
                new Assert\Type([
                    'type' => 'string',
                    'message' => 'Name must be a string.',
                ]),
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Name cannot be longer than 100 characters.',
                ]),
            ],
            'price' => [
                new Assert\NotBlank([
                    'message' => 'Price cannot be blank.',
                ]),
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'Price must be a number.',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'Price must be greater than or equal to 0.',
                ]),
            ],
            'stock' => [
                new Assert\NotBlank([
                    'message' => 'Stock cannot be blank.',
                ]),
                new Assert\Type([
                    'type' => 'numeric',
                    'message' => 'Stock must be a number.',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'Stock must be greater than or equal to 0.',
                ]),
            ],
        ]));

        if (count($violations) == 0)
        {
            if (!$id) {
                $violations->add($this->validatorHelper->buildConstraintViolation('Id must be an integer.', 'id', $command->id));
            }
            else
            {
                if (!$this->repository->find($id)) {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Id does not exist.', 'id', $command->id));
                }

                if ($this->repository->existsByName($command->name, $id)) {
                    $violations->add($this->validatorHelper->buildConstraintViolation('Name already exists.', 'name', $command->name));
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
