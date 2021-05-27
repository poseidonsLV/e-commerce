<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\SoldProductsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminController extends AbstractController {
    /**
     * @Route("/admin/dashboard", name="admin_dashboard")
     */
    public function index(TokenStorageInterface $tokenStorage, ProductRepository $productRepository, SoldProductsRepository $soldProductsRepository): Response {
        $topSellingProducts = [];
        $totalProductsSold = 0;
        $totalProductsSoldCount = 0;

        $user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        
        if ($user === 'anon.' || $user->getDashboardLogged() === false) {
            return $this->redirectToRoute('admin_login');
        }

        $allProducts = $productRepository->findAll();
        
        // Single top selling product

        $mostSoldProductId = (int)$soldProductsRepository->getMostSoldProduct(1)[0]['product_id'];
        $allMostSoldProducts = $soldProductsRepository->findBy(['productID' => $mostSoldProductId]);
        $mostSoldProduct = $productRepository->findBy(['id' => $mostSoldProductId]);
        $mostSoldProductTotal = $this->calculateBestSellingProductRevenue($allMostSoldProducts, $mostSoldProduct);

        // Multiple ( 5 ) top selling products
        $mostSoldProducts = $soldProductsRepository->getMostSoldProduct(5);

        foreach($mostSoldProducts as $mostSoldPid) {
            
            $mostSoldProduct = $productRepository->findBy(['id' => $mostSoldPid['product_id']]);
            $products = $soldProductsRepository->findBy(['productID' => $mostSoldProductId]);
            $mostSoldProductCount = 0;
            
            foreach($products as $product) {
                $totalProductsSold += (int)$product->getCount();
                $price = (int)$mostSoldProduct[0]->getPrice();
                $count = (int)$product->getCount();
                $mostSoldProductCount = $price * $count;
            }
            
            $mostSoldProduct[0]->setPrice($mostSoldProductCount);
            array_push($topSellingProducts, $mostSoldProduct[0]);
        }
        
        // Get total products sold
        $allSoldProducts = $soldProductsRepository->findAll();
        foreach($allSoldProducts as $sProduct) {
            $totalProductsSoldCount =  $totalProductsSoldCount + $sProduct->getCount();
        }
        
        return $this->render('admin/index.html.twig', [
            'allProducts' => count($allProducts),
            'topSellingProducts' => $topSellingProducts,
            'mostSoldProductInfo' => [[$mostSoldProduct[0]],
            'totalRevenue' => $mostSoldProductTotal],
            'totalProductsSold' => $totalProductsSoldCount]);
        
    }

    /**
     * @Route("/admin/login", name="admin_login")
     */
    public function login(Request $request, TokenStorageInterface $tokenStorage, UserRepository $userRepository): Response {
        $dashboardUsername = 'admin';
        $dashboardPassword = 'admin';
        $user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        if ($user->getAdmin() !== true) {
            return $this->redirectToRoute('home');
        }

        if ($request->getMethod('POST')) {
            
            $username = $request->get('username');
            $password = $request->get('password');
           
            
            if ($dashboardUsername === $username && $dashboardPassword === $password) {
                $user->setDashboardLogged(true);
                $userRepository->saveDashboardLogged($user);
                return $this->redirectToRoute('admin_dashboard');
            } else {
                $user->setDashboardLogged(false);
                $userRepository->saveDashboardLogged($user);
            }

        }

        return $this->render('admin/login.html.twig');
    }

    /**
     * @Route("/admin/dashboard/products", name="admin_dashboard_products")
     */
    public function products(ProductRepository $productRepository) {
        
        $products = $productRepository->findAll();

        return $this->render('admin/products.html.twig', ['products' => $products]);
    }
    /**
     * @Route("/admin/dashboard/products/add", name="dashboard_product-add")
     */
    public function addProduct(Request $request, ProductReposiry $productRepository){

        if ($request->getMethod() === 'POST') {
            $category = $request->get('category');
            $name = $request->get('name');
            $title = $request->get('title');
            $description = $request->get('description');
            $price = $request->get('price');
            $image = $request->get('image');

            // Create product
            $status = $productRepository->createProduct($category,$name,$title,$description,$price,$image);

            if ($status) {
                return $this->redirectToRoute('admin_dashboard_products');
            } else {
                return $this->redirectToRoute('dashboard_product-add');
            }

        }

        return $this->render('admin/productAdd.html.twig');
    }

    /**
     * @Route("/admin/dashobard/product/{productID}/delete", name="dashboard_product-delete")
     */

    public function deleteProduct($productID,ProductRepository $productRepository) {

        $productRepository->deleteProduct($productID);
        return $this->redirectToRoute('admin_dashboard_products'); 

    }

    /**
     * @Route("/admin/dashobard/product/{productID}/edit", name="dashboard_product-edit")
     */

    public function editProduct($productID,ProductRepository $productRepository, Request $request) {

        $product = $productRepository->findBy(['id' => $productID]);
        
        if (count($product) < 1) {
            return $this->redirectToRoute('admin_dashboard_products');
        } 

        if ($request->getMethod() === 'POST') {
            $category = $request->get('category');
            $name = $request->get('name');
            $title = $request->get('title');
            $description = $request->get('description');
            $price = $request->get('price');
            $image = $request->get('image');

            $productRepository->updateProduct($category,$name,$title,$description,$price,$image, $product[0]);

            return $this->redirectToRoute('admin_dashboard_products'); 
        };

        return $this->render('admin/productEdit.html.twig', ['product' => $product[0]]);

    }
     
    public function calculateBestSellingProductRevenue($allMostSoldProducts, $mostSoldProduct){
        $totalRevenueFromMostSoldProduct = 0;


        foreach($allMostSoldProducts as $sProd) {
            $totalRevenueFromMostSoldProduct += $sProd->getCount();
        }
        return $totalRevenueFromMostSoldProduct * (int)$mostSoldProduct[0]->getPrice();
        
    }
}
