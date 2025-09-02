<?php

namespace UserBundle\UI\Http\Controller;

use DateTimeImmutable;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class RefreshTokenController
{
    public function __construct(
        private RefreshTokenManagerInterface $refreshTokens,
        private JWTTokenManagerInterface $jwt,
        private UserProviderInterface $userProvider,
        private string $refreshTtl = '+30 days',
        private bool $singleUse = true,
    ) {}


    #[Route('token/refresh', name: 'users_token_refresh', methods: ['POST'])]
    #[OA\Post(
    path: '/api/users/token/refresh',
    summary: 'Genera un nuevo access token a partir de un refresh token',
    tags: ['Users'],
    security: [],
    requestBody: new OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
    required: ['refresh_token'],
    properties: [
    new OA\Property(property: 'refresh_token', type: 'string', example: 'eyJ...'),
    ],
    type: 'object'
    )
    ),
    responses: [
    new OA\Response(response: 200, description: 'OK'),
    new OA\Response(response: 400, description: 'Missing refresh_token'),
    new OA\Response(response: 401, description: 'Invalid/Expired refresh token'),
    ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $provided = $data['refresh_token'] ?? null;

        if (!\is_string($provided) || $provided === '') {
            return new JsonResponse(['code' => 400, 'message' => 'Missing refresh_token'], 400);
        }

        $refresh = $this->refreshTokens->get($provided);
        if (!$refresh) {
            return new JsonResponse(['code' => 401, 'message' => 'Invalid refresh token'], 401);
        }

        $now = new DateTimeImmutable();
        if ($refresh->getValid() < $now) {
            $this->refreshTokens->delete($refresh);
            return new JsonResponse(['code' => 401, 'message' => 'Expired refresh token'], 401);
        }

        $identifier = $refresh->getUsername();
        try {
            $user = $this->userProvider->loadUserByIdentifier($identifier);
        } catch (UserNotFoundException) {
            $this->refreshTokens->delete($refresh);
            return new JsonResponse(['code' => 401, 'message' => 'Unknown user for this token'], 401);
        }

        if (!$user instanceof UserInterface) {
            return new JsonResponse(['code' => 500, 'message' => 'User provider did not return a valid UserInterface'], 500);
        }

        $accessToken = $this->jwt->create($user);
        $response['data'] = ['token' => $accessToken];

        if ($this->singleUse) {
            $this->refreshTokens->delete($refresh);

            $newRefresh = $this->refreshTokens->create();
            $newRefresh->setUsername($identifier);
            $newRefresh->setRefreshToken();
            $newRefresh->setValid(new DateTimeImmutable($this->refreshTtl));
            $this->refreshTokens->save($newRefresh);

            $response['data']['refresh_token'] = $newRefresh->getRefreshToken();
        }

        return new JsonResponse($response, 200);
    }
}
