<?php

namespace App\Controller;

use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ProductRepository $productRepository, TokenStorageInterface $tokenStorage, CartRepository $cartRepository): Response {
        $user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        if ($user === 'anon.') {
            return $this->redirectToRoute('app_login');
        }
        $cartProducts = $cartRepository->getCartItems($user->getId());
        

        $cartCount = count($cartProducts);

        $allProducts = $productRepository->findAll();
        $trendingProducts = $productRepository->findBy(['trending' => true]);
        
        return $this->render('main/index.html.twig', ['allProducts' => $allProducts, 'trendingProduct' => $trendingProducts, 'cartCount' => $cartCount]);
    }
}