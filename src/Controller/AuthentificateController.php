<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AuthentificateController extends AbstractController
{
    #[Route('/account', name: 'register', methods: ['POST'])]
    public function register(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $user->setUsername($data['username'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setFirstname($data['firstname']);
        
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User is created'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/token', name: 'login', methods: ['POST'])]
    public function login(Request $request,  UserRepository $userRepository , JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $userRepository->findOneBy(['email' => $data['email']]);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }
  
        $token = $jwtManager->create($user);
        return new JsonResponse(['token' => $token]);

    }
}
