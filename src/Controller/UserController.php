<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'app_user', methods: ['GET'])]
    public function getAllUser(UserRepository $userRepos, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepos->findAll();

        $userJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

        return new JsonResponse($userJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users', name: 'create_user', methods: ['POST'])]
    public function createUser(UserRepository $userRepos, 
        UserPasswordHasherInterface $userPasswordHasher,Request $request,
        SerializerInterface $serializer): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $content = $request->toArray();
        $user->setEmail($content['username']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $content['password']));
        $userRepos->save($user, true);

        $userJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

        return new JsonResponse($userJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/users/{id}', name: 'edit_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droit necessaire pour modifier un utilisateur')]
    public function editUser(UserRepository $userRepos,
        User $user, 
        ValidatorInterface $validator,
        Request $request, UserPasswordHasherInterface $userPasswordHasher,
        SerializerInterface $serializer): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), 
            User::class, 'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        $content = $request->toArray();
        $error = $validator->validate($user);
        if($error->count() > 0){
            $userJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);
            return new JsonResponse($userJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        
        $user->setPassword(
            $userPasswordHasher->hashPassword($user, $content['password']));
        $userRepos->save($user, true);
        $userJson = $serializer->serialize($user, 'json', ['groups' => 'getUser']);

        return new JsonResponse($userJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/users/{id}', name: 'dele_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', massage: 'Vous n\'avez pas les droits necessaires pour supprimer un utilisateur.')]
    public function deleteUser(UserRepository $userRepos, User $user): JsonResponse
    {
        $userRepos->remove($user, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
