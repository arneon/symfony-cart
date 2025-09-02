<?php

namespace UserBundle\UI\Http\Controller;

use UserBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Application\UseCases\LoginUser\LoginUserCommand as CommandUseCase;
use UserBundle\Application\UseCases\LoginUser\LoginUserHandler as Handler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class LoginUserController
{
    public function __construct(
        private Handler $handler,
    ) {}

    #[OA\Post(
        path: '/api/users/login',
        summary: 'Login User',
        tags: ['Users'],
        security: [],
        requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
        type: 'object',
        required: ['email', 'password'],
        properties: [
        new OA\Property(
        property: 'email',
        type: 'email',
        example: 'user@mail.com'
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
                response: 200,
                description: 'User logged successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Error logging user'
            )
        ]
    )]
    #[Route('/login', name: 'login_user', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new CommandUseCase(
                ($data['email'] ?? null),
                ($data['password'] ?? null)
            );
        }catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }

        try {
            $response = $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'User logged successfully', 'data' => $response]);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }
    }
}
