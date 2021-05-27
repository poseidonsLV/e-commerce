<?php
namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    public function createProduct($category, $name,$title,$description,$price,$image){
        $product = new Products();

        $product->setCategory($category);
        $product->setName($name);
        $product->setTitle($title);
        $product->setDescription($description);
        $product->setPrice($price);
        $product->setImage($image);
        $product->setTrending(0);


        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();
        return true;
    }

    public function updateProduct($category, $name,$title,$description,$price,$image, $product){

        $product->setCategory($category);
        $product->setName($name);
        $product->setTitle($title);
        $product->setDescription($description);
        $product->setPrice($price);
        $product->setImage($image);
        $product->setTrending(0);

        $em = $this->getEntityManager();
        $em->persist($product);
        $em->flush();
        return true;
    }
    
    public function deleteProduct($productID) {
        $em = $this->getEntityManager();
        $product = $this->findBy(['id' => $productID])[0];
        $em->remove($product);
        $em->flush();
        return true;
    }

}