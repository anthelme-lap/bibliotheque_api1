<?php
namespace App\Service;

use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartServices{

    public function __construct(
        private BookRepository $bookRepos,
        private RequestStack $requestStack){}

    /**
     * On récupère le panier
     * Verifions si le produit existe dans le panier
     * Si le produit existe on incremente de sorte qu'il puisse augmenter la quantité de ce produit
     * sinon on retourne 1
     * Enfin on met a jour le panier
    */
    public function addToCart($id){
        $cart = $this->getCart();

        if(isset($cart[$id])){
            $cart[$id]++;
        }else{
            $cart[$id] = 1;
        }

        $this->updatedCart($cart);

    }

     /**
     * On récupère le panier
     * Verifions si le produit existe dans le panier
     * Si le produit existe et qu'il est superieur a 1 on décremente
     * sinon on retire tout le produit
     * Enfin on met a jour le panier
    */
    public function reduceProdQtCart($id){
        $cart = $this->getCart();
        if(isset($cart[$id])){
            if($cart[$id] > 1){
                $cart[$id]--;
            }else{
                unset($cart[$id]);
            }
        }
        $this->updatedCart($cart);
    }

    public function emptyCart(){
        $this->updatedCart([]);
    }

    public function updatedCart($cart){
       $session = $this->requestStack->getSession();
       $session->set('cart', $cart);
    }

    public function getCart(){
       $session = $this->requestStack->getSession();
       return $session->get('cart',[]);
    }


    public function getFullcart(){
        $cart = $this->getCart();
        $fullcart = [];
        foreach ($cart as $id => $quantity) {
            $book = $this->bookRepos->find($id);
            if($book){
                $fullcart[] = [
                    'quantity' => $quantity,
                    'book' => $book
                ];
                $this->updatedCart($cart);
            }
            
            
        }
        return $fullcart;
    }

}