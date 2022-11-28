<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/api/comments', name: 'app_comment', methods: ['GET'])]
    public function getAllComment(CommentRepository $commentRepos, SerializerInterface $serializer): JsonResponse
    {
        $comment = $commentRepos->findAll();

        $commentJson = $serializer->serialize($comment, 'json', ['groups' => 'getComment']);

        return new JsonResponse($commentJson,Response::HTTP_ACCEPTED,[],true);
    }

    #[Route('/api/comments', name: 'create_comment', methods: ['POST'])]
    public function createComment(Request $request, CommentRepository $commentRepos, SerializerInterface $serializer): JsonResponse
    {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $user = $this->getUser();
        $content = $request->toArray();
        $comment->setUser($user);
        $comment->setContent($user);
        $comment->setUser($user);
        $commentJson = $serializer->serialize($comment, 'json', ['groups' => 'getComment']);

        return new JsonResponse($commentJson,Response::HTTP_ACCEPTED,[],true);
    }
}
