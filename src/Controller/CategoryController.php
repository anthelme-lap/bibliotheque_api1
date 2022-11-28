<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

class CategoryController extends AbstractController
{
    #[Route('/api/category', name: 'category', methods: ['GET'])]
    public function getCategoryList(CategoryRepository $categoryRepos,
    SerializerInterface $serializer, Request $request,
    PaginatorInterface $paginator): JsonResponse
    {
        $category = $paginator->paginate(
            $categoryRepos->findBy([],['createdAt' => 'DESC']),
            $request->query->getInt('page', 1),
            4
        );
        
        $categoryJson = $serializer->serialize($category, 'json', ['groups' => 'getCategory']);

        return new JsonResponse($categoryJson, Response::HTTP_OK, [], true);
    }

    #[Route('/api/category/{id}', name: 'detailCategory', methods: ['GET'])]
    public function getCategory(SerializerInterface $serializer, Category $category): JsonResponse
    {
        if($category){
            $categoryJson = $serializer->serialize($category, 'json', ['groups' => 'getCategory']);
            return new JsonResponse($categoryJson, Response::HTTP_OK, [], true);
        }
    }

    #[Route('/api/category', name: 'createCategory', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits necessaires pour créer une catégorie.')]
    public function createCategory(CategoryRepository $categoryRepos,
    SerializerInterface $serializer, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');
           
        $error = $validator->validate($category);
        if($error->count() > 0){
            return new JsonResponse(
                $serializer->serialize($error, 'json', [ 'groups' =>'getCategory']),
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        $categoryRepos->save($category, true);


        $categoryJson = $serializer->serialize($category, 'json', [ 'groups' =>'getCategory']);

        return new JsonResponse($categoryJson, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/category/{id}', name: 'editCategory', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits necessaires pour modifier une catégorie.')]
    public function editCategory(CategoryRepository $categoryRepos,
    SerializerInterface $serializer, Request $request, ValidatorInterface $validator,
    Category $currentCategory): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json',
        [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]);
        
        $error = $validator->validate($category);
        if($error->count() > 0){
            return new JsonResponse(
                $serializer->serialize($error, 'json'),
                Response::HTTP_BAD_REQUEST, [], true
            );
        }
        $categoryRepos->save($category, true);

        // $categoryJson = $serializer->serialize($category, 'json', [ 'groups' =>'getCategory']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/category/{id}', name: 'deleteCategory', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', massage: 'Vous n\'avez pas les droits necessaires pour supprimer une catégorie.')]
    public function deleteCategory(CategoryRepository $categoryRepos, Category $category): JsonResponse
    {
        $categoryRepos->remove($category, true);
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Récupération des articles en fonction des catégories
    #[Route('/api/books/category/{id}', name: 'bookListCategory', methods:['GET'])]
    public function getBookListCategory(Category $category, 
    SerializerInterface $serializer,
    CategoryRepository $categoryRepos): JsonResponse
    {
        if($category){
            $bookList = $categoryRepos->getBookListByCategory($category->getId());
            // dd($bookList);
            $bookListJson = $serializer->serialize($bookList, 'json', ['groups' => 'getBook']);
            return new JsonResponse($bookListJson, Response::HTTP_OK,[],true);
        }
        else{
            $bookList = $categoryRepos->getBookListByCategory($category);
            $bookListJson = $serializer->serialize($bookList, 'json', ['groups' => 'getBook']);
            return new JsonResponse($bookListJson, Response::HTTP_BAD_REQUEST,[],true);
        }
            
    }
}
