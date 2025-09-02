<?php

namespace UserBundle\Application\UseCases\UpdateUser;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Infrastructure\Persistence\Mysql\DoctrineUserRepository as Repository;
use UserBundle\Domain\Exception\ValidationException;
use App\Helper\ValidatorHelper;

class UpdateUserValidator
{
    public function __construct(
        private Repository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(UpdateUserCommand $command): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate([
            'id' => $command->id ?? null,
            'name' => $command->name ?? null,
            'email' => $command->email ?? null,
//            'password' => $command->password ?? null,
            'roles' => $command->roles ?? [],
        ],
            new Assert\Collection([
                'id' => [
                    new Assert\NotBlank(message: 'Id cannot be blank.'),
                    new Assert\Type(type: 'integer', message: 'Id must be an integer.'),
                ],
                'name' => [
                    new Assert\NotBlank(message: 'Name cannot be blank.'),
                    new Assert\Type(type: 'string', message: 'Name must be a string.'),
                    new Assert\Length(max: 100, maxMessage: 'Name cannot be longer than 100 characters.'),
                ],
                'email' => [
                    new Assert\NotBlank(message: 'Email cannot be blank.'),
                    new Assert\Email(['message' => 'Email must be email type.']),
                    new Assert\Length(max: 100, maxMessage: 'Email cannot be longer than 100 characters.'),
                ],
                'roles' => [
                    new Assert\Type('array'),
                    new Assert\Count(min: 0, minMessage: 'Roles must be an array of strings.'),
                    new Assert\All([
                        new Assert\Type('string'),
                        new Assert\Regex('/^ROLE_[A-Z_]+$/'),
                        // Si tienes un catálogo de roles permitido
                        // new Assert\Choice(['ROLE_USER','ROLE_ADMIN','ROLE_MANAGER']),
                    ]),
                ],
            ])
        );

        $user = $this->repository->findByEmail(new UserEmail($command->email), $command->id);
        if($user)
        {
            $violations->add($this->validatorHelper->buildConstraintViolation('Email already exists.', 'email', $command->email));
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
