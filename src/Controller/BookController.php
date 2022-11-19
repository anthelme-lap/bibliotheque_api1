<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    #[Route('/api/books', name: 'book', methods:["GET"])]
    public function getBooksList(Request $request,
    SerializerInterface $serializer, PaginatorInterface $paginator,
    BookRepository $bookRepos): JsonResponse
    {
        // On récupère la liste des auteurs

        $booksList = $paginator->paginate(
            $bookRepos->findBy([],['createdAt' => 'DESC']),
            $request->query->getInt('page', 1),
            2
        );
        
        // On Transforme la liste des auteurs (objet) en tableau Serialisation
        $bookJson = $serializer->serialize($booksList, 'json', ['groups' => 'getBook']);

        return new JsonResponse($bookJson, Response::HTTP_OK, [], true);
    }

    // NOMBRE TOTAL DE BOOK
    #[Route('/api/books/count', name: 'countBook', methods:["GET"])]
    public function countBook(BookRepository $bookRepos, SerializerInterface $serializer): JsonResponse
    {
        $count = $bookRepos->findCount();

        // dd($count);

        $countBookJson = $serializer->serialize($count, 'json');
        return new JsonResponse($countBookJson, Response::HTTP_OK, [], true);
    }

    // RECUPERE 4 DERNIERS BOOK
    #[Route('/api/books/lastBook', name: 'lastBook', methods:["GET"])]
    public function recentBook(BookRepository $bookRepos, SerializerInterface $serializer): JsonResponse
    {
        $lastBook = $bookRepos->findBy([],['createdAt' => 'DESC'],1);

        $lastBookJson = $serializer->serialize($lastBook, 'json', ['groups' => 'getBook']);

        return new JsonResponse($lastBookJson, Response::HTTP_OK, [], true);
    }



    #[Route('/api/books', name: 'createBook', methods:["POST"])]
    public function createbook(
        Request $request, ValidatorInterface $validator,
        AuthorRepository $authorRepos, 
        SerializerInterface $serializer,  CategoryRepository $categoryRepos,
        BookRepository $bookRepos): JsonResponse
    {
        // On Transforme l'entité author (objet) en json DESERIAlISATION
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $errors = $validator->validate($book);
        if($errors->count() > 0){
            return new JsonResponse(
                $serializer->serialize($errors, 'json'), 
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        $content = $request->toArray();

        // On récupère l'auteur 
        $author = $authorRepos->find($content['idAuthor']);

        // On récupère la categorie 
        $category = $categoryRepos->find($content['idCategory']);

        $category = $categoryRepos->find($content['idCategory']);


        // dd($author);
        $book->setAuthor($author);

        $book->setCategory($category);
        
        // On envoie les données dans la base de données
        $bookRepos->save($book, true);
        
        // On renvoie les données en json pour les afficher sur notre postman SERIAlISATION
        $bookJson = $serializer->serialize($book, 'json', ['groups' => 'getBook']);
        return new JsonResponse($bookJson, Response::HTTP_CREATED, [], true);
    }



    #[Route('/api/books/{id}', name: 'editBook', methods:["PUT"])]
    public function editbook(
        Request $request, ValidatorInterface $validator,
        BookRepository $bookRepos, SerializerInterface $serializer,
        Book $book): JsonResponse
    {
        $bookUpdated = $serializer->deserialize(
            $request->getContent(), 
            Book::class, 'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $book]
        );
        $errors = $validator->validate($bookUpdated);
        if($errors->count() > 0){
            return new JsonResponse(
                $serializer->serialize($errors, 'json'), 
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        $bookRepos->save($bookUpdated, true);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/books/{id}', name: 'detailBook', methods:["GET"])]
    public function show(SerializerInterface $serializer, Book $book): JsonResponse
    {
        if($book){
            $bookJson = $serializer->serialize($book, 'json',['groups' => 'getBook']);
            return new JsonResponse($bookJson, Response::HTTP_OK, [], true);
        }
    }

    #[Route('/api/books/{id}', name: 'deleteBokk', methods:["DELETE"])]
    public function deleteBook(BookRepository $bookRepos, Book $book): JsonResponse
    {
        $bookRepos->remove($book, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

   

}
