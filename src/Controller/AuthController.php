<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\UserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordCredentials;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserSessionRepository;



#[Route('/api/v1')]
class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
//    private JWTTokenManagerInterface
    private JWTTokenManagerInterface $jwtManager;
    private UserSessionRepository $userSessionRepository;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, JWTTokenManagerInterface $jwtManager,UserSessionRepository $userSessionRepository)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->jwtManager = $jwtManager;
        $this->userSessionRepository = $userSessionRepository;
    }

    // User Registration
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate incoming request
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $roles = $data['roles'] ?? ['ROLE_USER'];

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email is already in use'], JsonResponse::HTTP_CONFLICT);
        }

        $user = new Users();
        $user->setEmail($email);
        $user->setRoles($roles);

        //To Hash the password
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        //To Validate the User entity
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User registered successfully'], JsonResponse::HTTP_CREATED);
    }

    // User Login
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid login credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $token = $this->jwtManager->create($user);  // Updated line
            if (!$token) {
                throw new \Exception('Token creation returned null.');
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Token generation failed: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $session = new UserSession();
        $session->setUser($user);
        $session->setToken($token);
        $session->setCreatedAt(new \DateTime());
        $session->setExpiresAt((new \DateTime())->modify('+1 day'));

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    // Method to validate session based on token
    public function validateSession(string $token): ?Users
    {
        $session = $this->userSessionRepository->findValidSession($token);
        if (!$session) {
            throw new \Exception('Invalid or expired session');
        }
        return $session->getUser();
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        // Ensure the request is authenticated
        $tokenHeader = $request->headers->get('Authorization');

        if (!$tokenHeader || strpos($tokenHeader, 'Bearer ') !== 0) {
            return new JsonResponse(['message' => 'Token not provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $token = str_replace('Bearer ', '', $tokenHeader);  // Remove Bearer prefix

        // Find the session by the token and remove it
        $session = $this->entityManager->getRepository(UserSession::class)->findOneBy(['token' => $token]);

        if ($session) {
            $this->entityManager->remove($session);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Logged out successfully']);
        }

        // If the token is not found, return a more specific message
        return new JsonResponse(['message' => 'JWT Token not found'], JsonResponse::HTTP_UNAUTHORIZED);
    }

}
