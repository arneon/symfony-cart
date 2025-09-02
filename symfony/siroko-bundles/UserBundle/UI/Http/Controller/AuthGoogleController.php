<?php

namespace UserBundle\UI\Http\Controller;

//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Routing\Annotation\Route;
//use OpenApi\Attributes as OA;
//use UserBundle\Application\UseCases\LoginWithGoogle\LoginWithGoogleCommand as CommandUseCase;
//use UserBundle\Application\UseCases\LoginWithGoogle\LoginWithGoogleHandler as Handler;
//use UserBundle\Domain\Exception\ValidationException;

final readonly class AuthGoogleController
{
/*    public function __construct(
        private Handler $handler,
    ) {}

    #[Route('auth/google', name: 'auth_google', methods: ['POST'])]
    #[OA\Post(
        tags: ['Auth'],
        path: '/api/auth/google',
        summary: 'Login/Register with Google (ID Token)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'id_token', type: 'string')
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 400, description: 'Bad Request'),
            new OA\Response(response: 401, description: 'Invalid token'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $command = new CommandUseCase(
                ($data['id_token'] ?? null),
            );
        }catch (ValidationException $e) {
            return new JsonResponse(['errors' => $e->getErrors()], $e->getCode());
        }

        try {
            $result = $this->handler->__invoke($command);
            return new JsonResponse($result, 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }*/
}
