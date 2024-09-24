<?php
namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class JwtAuthenticator implements AuthenticatorInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private UserProviderInterface $userProvider;

    public function __construct(JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider)
    {
    $this->jwtManager = $jwtManager;
    $this->userProvider = $userProvider;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('Authorization');
        if (!$token || strpos($token, 'Bearer ') !== 0) {
            throw new BadCredentialsException('No token provided');
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $payload = $this->jwtManager->decode($token);
            if (isset($payload['email'])) {
                $user = $this->userProvider->loadUserByIdentifier($payload['email']);
                return new Passport(
                    new UserBadge($payload['email']),
                    new PasswordCredentials($token)
                );
            }

            throw new BadCredentialsException('Invalid token: Missing email');
        } catch (\Exception $e) {
            throw new BadCredentialsException('Invalid token: ' . $e->getMessage());
        }
    }

    public function onAuthenticationSuccess(Request $request, UserInterface|TokenInterface $user, string $providerKey): ?JsonResponse
    {
    return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
    return new JsonResponse(['message' => 'Authentication Failed'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): ?JsonResponse
    {
    return new JsonResponse(['message' => 'Authentication Required'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): ?bool
    {
    return $request->headers->has('Authorization');
    }

    public function supportsRememberMe(): bool
    {
    return false;
    }

    public function createToken(PassportInterface|Passport $passport, string $firewallName): TokenInterface
    {
    throw new \LogicException('Token creation is not supported in this implementation.');
    }
}