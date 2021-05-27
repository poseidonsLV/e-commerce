<?php

namespace App\Entity;

use App\Repository\SoldProductsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SoldProductsRepository::class)
 */
class SoldProducts
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $buyerID;

    /**
     * @ORM\Column(type="integer")
     */
    private $productID;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuyerID(): ?int
    {
        return $this->buyerID;
    }

    public function setBuyerID(int $buyerID): self
    {
        $this->buyerID = $buyerID;

        return $this;
    }

    public function getProductID(): ?int
    {
        return $this->productID;
    }

    public function setProductID(int $productID): self
    {
        $this->productID = $productID;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
