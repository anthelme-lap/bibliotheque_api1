<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Comment;
use App\Repository\BookRepository;
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

    #[Route('/api/{postId}/comments', name: 'create_comment', methods: ['POST'])]
    public function createComment(
        Request $request,
        BookRepository $bookRepos, 
        CommentRepository $commentRepos, 
        SerializerInterface $serializer): JsonResponse
    {
        $comment = $serializer->deserialize($request->getContent(), Comment::class, 'json');
        $user = $this->getUser();
        $content = $request->toArray();

        $bookId = $content['idBook'];
        $parentId = $content['parent'];
        $book = $bookRepos->find($bookId);

        if($parentId != null){
            $parent = $commentRepos->find($parentId);
        }
        else{
            $comment->setParent(null);
        }
        $comment->setParent($parent);
        
        $comment->setBook($book);
        $comment->setUser($user);
        

        $commentRepos->save($comment, true);

        $commentJson = $serializer->serialize($comment, 'json', ['groups' => 'getComment']);

        return new JsonResponse($commentJson,Response::HTTP_ACCEPTED,[],true);
    }
}
