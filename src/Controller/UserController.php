<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods:['GET'])]
    public function getUsersList(
        UserRepository $userRepos, 
        SerializerInterface $serializer
        ): JsonResponse
    {
        $users = $userRepos->findAll();

        $userJson = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($userJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/register', name: 'create_user', methods:['POST'])]
    public function createUser(
        UserRepository $userRepos, Request $request, 
        SerializerInterface $serializer, UserPasswordHasherInterface $userPasswordHasher,
        ): JsonResponse
    {
        $users = $serializer->deserialize($request->getContent(), User::class, 'json');

        $content = $request->toArray();
        $email = $content['email'];
        $password = $content['password'];
        // $roles = $content['roles'];
        // dd($content);
        $users->setEmail($email);
        $users->setPassword($userPasswordHasher->hashPassword($users, $password));
        // $users->setEmail($roles);

        $userRepos->save($users, true);
        
        $userJson = $serializer->serialize($users, 'json', ['groups' => 'getUsers']);

        return new JsonResponse($userJson, Response::HTTP_CREATED, [], true);
    }
}
