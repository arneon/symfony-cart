<?php

namespace UserBundle\Application\UseCases\LoginUser;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use UserBundle\Application\UseCases\LoginUser\LoginUserCommand as Command;
use UserBundle\Domain\ValueObject\UserEmail;
use UserBundle\Infrastructure\Persistence\Mysql\DoctrineUserRepository as Repository;
use UserBundle\Domain\Exception\ValidationException;
use App\Helper\ValidatorHelper;

class LoginUserValidator
{
    public function __construct(
        private Repository $repository,
        private ValidatorHelper $validatorHelper,
    ) {}

    public function validate(Command $command): void
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate([
            'email' => $command->email ?? null,
            'password' => $command->password ?? null,
        ],
            new Assert\Collection([
                'email' => [
                    new Assert\NotBlank(message: 'Email cannot be blank.'),
                    new Assert\Email(['message' => 'Email must be email type.']),
                    new Assert\Length(max: 100, maxMessage: 'Email cannot be longer than 100 characters.'),
                ],
                'password' => [
                    new Assert\NotBlank(message: 'Password cannot be blank.'),
                    new Assert\Type(type: 'string', message: 'Password must be a string.'),
                    new Assert\Length(min: 8, maxMessage: 'Password must be at least 8 characters long.'),
                    new Assert\Length(max: 30, maxMessage: 'Password cannot be longer than 30 characters.'),
                ],
            ])
        );

        $user = $this->repository->findByEmail(new UserEmail($command->email));
        if(!$user)
        {
            $violations->add($this->validatorHelper->buildConstraintViolation('Invalid credentials.', 'email', $command->email));
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
