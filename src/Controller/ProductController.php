<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
    /**
     * @Route("/product/{productID}/info", name="single_product")
     */
    public function single(ProductRepository $productRepository, $productID, CartRepository $cartRepository, UserInterface $user): Response {
        $cartProducts = $cartRepository->getCartItems($user->getId());
        $cartCount = count($cartProducts);
        $singleProduct = $productRepository->find(['id' => $productID]);
        $allProducts = $productRepository->findBy(array(),array(),4);
        return $this->render('product/single.html.twig', ['product' => $singleProduct, 'allProducts' => $allProducts, 'cartCount' => $cartCount]);
    }
}
