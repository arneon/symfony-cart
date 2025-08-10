<?php

namespace ProductBundle\Application\UseCases\DeleteProduct;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Domain\Exception\ValidationException;
use App\Helper\ValidatorHelper;

class DeleteProductValidator
{
    public function __construct(
        private DoctrineProductRepository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(DeleteProductCommand $command): void
    {
        $id = filter_var($command->id, FILTER_VALIDATE_INT);

        $validator = Validation::createValidator();
        $violations = $validator->validate($id,
            [
                new Assert\GreaterThan(['value' => 0, 'message' => 'Id must be greater than 0.']),
            ]
        );

        if (count($violations) == 0)
        {
            if (!$this->repository->find($id)) {
                $violations->add($this->validatorHelper->buildConstraintViolation('Id does not exist.', 'id', $command->id));
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
