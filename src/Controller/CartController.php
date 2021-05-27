<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\SoldProducts;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/product/{productID}/addToCart", name="product_addToCart")
     */
    public function index(UserInterface $user, $productID, Request $request, CartRepository $cartRepository) {
        $productExists = $cartRepository->findBy(['pid' => $productID]);
        
        
        $em = $this->getDoctrine()->getManager();
        $uid = $user->getId();
        
        
        if (count($productExists) > 0) {
            $product = $productExists[0];
            $currCount = $product->getCount();
            $product->setCount($currCount + 1);
            $em->persist($product);
            $em->flush();
        } else {
            $cart = new Cart();
            $cart->setPid($productID);
            $cart->setUid($uid);
            $cart->setCount(1);
            $em->persist($cart);
            $em->flush();
        }


        return $this->redirect($request->headers->get('referer'));

    }
    /**
     * @Route("/cart", name="user_cart")
     */
    public function cart(UserInterface $user, CartRepository $cartRepository, ProductRepository $productRepository, Request $request){
        $uid = $user->getId();
        $cartProducts = $cartRepository->getCartItems($uid);
        $cartCount = count($cartProducts);
        $products = array();
        $totalPrice = null;

        foreach($cartProducts as $cProduct) {
            // find product
            $tempProduct = $productRepository->findBy(['id' => $cProduct->getPid()])[0];
            // set count key for product
            $tempProduct->count = $cProduct->getCount();
            // Get product price and multiply it with count of the product
            $totalProductPrice = (int)$tempProduct->getPrice() * $cProduct->getCount();
            // add totalProductPrice to totalPrice for all products
            $totalPrice += $totalProductPrice;
            // add totalPrice key which contains product total price.
            $tempProduct->totalPrice = $totalProductPrice;
            $products[] = $tempProduct;
        }

        if ($request->getMethod() === "POST") {
            $em = $this->getDoctrine()->getManager();
            foreach($cartProducts as $cProduct) {
                $soldProduct = new SoldProducts();
                $soldProduct->setBuyerID($uid);
                $soldProduct->setProductID($cProduct->getPid());
                $soldProduct->setCount($cProduct->getCount());

                $em->persist($soldProduct);
                $em->remove($cProduct);
                $em->flush();
            }
            return $this->redirectToRoute('user_cart');

        }

        return $this->render('cart/index.html.twig', ['products' => $products, 'cartCount' => $cartCount, 'totalPrice' => $totalPrice]);
    }

    /**
     * @Route("/delete/cart/product/{productID}", name="delete_cart-product")
     */

    public function deleteCartProduct($productID, Request $request,UserInterface $user, CartRepository $cartRepository) {

        $em = $this->getDoctrine()->getManager();
        $uid = $user->getId();
        $productToDelete = $cartRepository->findBy(['pid' => $productID, 'uid' => $uid])[0];
        $em->remove($productToDelete);
        $em->flush();
        
        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/increase/product/{productID}/count", name="increase_count")
     */
    public function increaseCartProductCount($productID,CartRepository $cartRepository, Request $request, UserInterface $user){
            $uid = $user->getId();
            $productExists = $cartRepository->findBy(['pid' => $productID, 'uid' => $uid]);
        	
            if ($productExists) {
                $em = $this->getDoctrine()->getManager();

                $product = $productExists[0];
                $currCount = $product->getCount();
                $product->setCount($currCount + 1);
                $em->persist($product);
                $em->flush();

                return $this->redirect($request->headers->get('referer'));
            }
    }

        /**
     * @Route("/decrease/product/{productID}/count", name="decrease_count")
     */
    public function decreaseCartProductCount($productID,CartRepository $cartRepository, Request $request, UserInterface $user){
        $uid = $user->getId();

        $productExists = $cartRepository->findBy(['pid' => $productID, 'uid' => $uid]);
        
        if ($productExists) {
            $em = $this->getDoctrine()->getManager();

            $product = $productExists[0];
            $currCount = $product->getCount();
            $product->setCount($currCount - 1);
            if ($product->getCount() <= 0) {
                $em->remove($product);
                $em->flush();
            }  else {
                $em->persist($product);
                $em->flush();
            }

            return $this->redirect($request->headers->get('referer'));
        }
    }

}
