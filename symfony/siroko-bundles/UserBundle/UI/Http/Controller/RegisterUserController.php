<?php

namespace UserBundle\UI\Http\Controller;

use UserBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Application\UseCases\RegisterUser\RegisterUserCommand as CommandUseCase;
use UserBundle\Application\UseCases\RegisterUser\RegisterUserHandler as Handler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class RegisterUserController
{
    public function __construct(
        private Handler $handler,
    ) {}

    #[Route('register', name: 'register_user', methods: ['POST'])]
    #[OA\Post(
        path: '/api/users/register',
        summary: 'Register User',
        tags: ['Users'],
        security: [],
        requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
        type: 'object',
        required: ['name', 'email', 'password'],
        properties: [
        new OA\Property(
        property: 'name',
        type: 'string',
        example: 'John Doe'
        ),
        new OA\Property(
        property: 'email',
        type: 'string',
        format: 'email',
        example: 'john.doe@mail.com'
        ),
        new OA\Property(
        property: 'password',
        type: 'string',
        format: 'password',
        example: '********'
        )
        ]
        )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Error registering user'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new CommandUseCase(
                ($data['name'] ?? null),
                ($data['email'] ?? null),
                ($data['password'] ?? null)
            );
        }catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }

        try {
            $response = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'User registered successfully', 'data' => $response]);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }
    }
}
