<?php

namespace App\Controller;

use App\Service\CartServices;
use OpenApi\Annotations\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

class CartController extends AbstractController
{
    #[Route('/api/cart', name: 'app_cart', methods:["GET"])]
    public function index(CartServices $cartService,SerializerInterface $serializer): JsonResponse
    {
        // dd($cartService->getFullCart());
        $cartJson = $serializer->serialize($cartService->getFullCart(), 'json', ['groups' => 'getCart']);
        return new JsonResponse($cartJson, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/cart/add/{id}', name: 'add_cart', methods:["GET"])]
    public function cart($id, CartServices $cartService,SerializerInterface $serializer): JsonResponse
    {
        $cartService->addToCart($id);
        $cartJson = $serializer->serialize($cartService->getFullCart(), 'json', ['groups' => 'getCart']);
        return new JsonResponse($cartJson, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/cart/reduceQt/{id}', name: 'reduceQte_cart', methods:["GET"])]
    public function reduceQte($id, CartServices $cartService,SerializerInterface $serializer): JsonResponse
    {
        $cartService->reduceProdQtCart($id);
        $cartJson = $serializer->serialize($cartService->getFullCart(), 'json', ['groups' => 'getCart']);
        return new JsonResponse($cartJson, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/cart/empty', name: 'empty_cart', methods:["GET"])]
    public function emptyCart(CartServices $cartService,SerializerInterface $serializer): JsonResponse
    {
        $cartService->emptyCart();
        $cartJson = $serializer->serialize($cartService->getFullCart(), 'json', ['groups' => 'getCart']);
        return new JsonResponse($cartJson, JsonResponse::HTTP_OK, [], true);
    }
}
