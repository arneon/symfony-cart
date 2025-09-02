<?php

namespace UserBundle\UI\Http\Controller;

use UserBundle\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use UserBundle\Application\UseCases\UpdateUser\UpdateUserCommand as CommandUseCase;
use UserBundle\Application\UseCases\UpdateUser\UpdateUserHandler as Handler;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateUserController
{
    public function __construct(
        private Handler $handler,
    ) {}

    #[Route('{id}/update', name: 'update_user', methods: ['PUT'])]
    #[IsGranted('LET_USER_WRITE')]
    #[OA\Put(
        path: '/api/users/{id}/update',
        summary: 'Update User',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User Id',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
        type: 'object',
        required: ['name', 'email', 'roles'],
        properties: [
        new OA\Property(
        property: 'name',
        type: 'string',
        example: 'John Doe'
        ),
        new OA\Property(
        property: 'email',
        type: 'email',
        example: 'john.doe@mail.com'
        ),
        new OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(type: 'string'),
            description: 'Roles',
            example: '["ROLE_USER", "ROLE_ADMIN"]'
        )
        ]
        )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User updated successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Error updating user'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new CommandUseCase(
                $request->attributes->get('id') ?? null,
                ($data['name'] ?? null),
                ($data['email'] ?? null),
                ($data['password'] ?? null),
                ($data['enabled'] ?? null),
                ($data['roles'] ?? [])
            );
        }catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }

        try {
            $this->handler->__invoke($command);
            return new JsonResponse(['status' => 'User updated successfully']);
        } catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }
    }
}
