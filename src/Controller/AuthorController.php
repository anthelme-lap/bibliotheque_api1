<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthorController extends AbstractController
{
    #[Route('/api/authors', name: 'author', methods: ["GET"])]
    public function getAuthorsList(Request $request,
        PaginatorInterface $paginator,
        SerializerInterface $serializer, 
        AuthorRepository $authorRepos): JsonResponse
    {
        // On récupère la liste des auteurs
        $authorsList = $authorRepos->findAll();

        $author = $paginator->paginate(
            $authorsList,
            $request->query->getInt('page', 1),
            2
        );

        // On Transforme la liste des auteurs (objet) en tableau Serialisation
        $authorJson = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);

        return new JsonResponse($authorJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/authors/{id}', name: 'detailAuthor', methods: ["GET"])]
    public function getDetailAuthor(SerializerInterface $serializer, Author $author): JsonResponse
    {
        
        if($author){
            $authorJson = $serializer->serialize($author, 'json', ['groups' => 'getAuthor']);
            return new JsonResponse($authorJson, Response::HTTP_OK, [], true);
        }
        
    }

    #[Route('/api/authors/{id}', name: 'editAuthor', methods: ["PUT"])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits necessaires pour modifier un auteur.')]
    public function editAuthor(
        Request $request, ValidatorInterface $validator,
        AuthorRepository $authorRepos, SerializerInterface $serializer,
        Author $author): JsonResponse
    {
        $authorUpdated = $serializer->deserialize(
            $request->getContent(), 
            Author::class, 'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $author]
        );
        $errors = $validator->validate($authorUpdated);
        if($errors->count() > 0){
            return new JsonResponse(
                $serializer->serialize($errors, 'json', ['groups'=> 'getAuthor']), 
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        $authorRepos->save($authorUpdated, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/authors/{id}', name: 'deleteAuthor', methods: ["DELETE"])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits necessaires pour supprimer un auteur.')]
    public function deleteAuthor( 
    AuthorRepository $authorRepos,
    Author $author): JsonResponse
    {
        $authorRepos->remove($author, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    
    #[Route('/api/authors', name:"createAuthor", methods: ["POST"])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits necessaires pour créer un auteur.')]
    public function createAuthor(Request $request, 
    SerializerInterface $serializer, 
    EntityManagerInterface $em, ValidatorInterface $validator,
    UrlGeneratorInterface $urlGenerator): JsonResponse 
    {

        $book = $serializer->deserialize($request->getContent(), Author::class, 'json');
        
        $errors = $validator->validate($book);
        if($errors->count() > 0){
            return new JsonResponse(
                $serializer->serialize($errors, 'json', ['groups'=> 'getAuthor']), 
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        
        $em->persist($book);
        $em->flush();
        $jsonBook = $serializer->serialize($book, 'json', ['groups'=> 'getAuthor']);
        
        $location = $urlGenerator->generate('detailAuthor', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
   }
    
    
}
