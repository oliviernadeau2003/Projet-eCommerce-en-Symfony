<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column("idPurchase")]
    private ?int $idPurchase = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Produit::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idProduit', referencedColumnName: 'idProduit', nullable: false)]
    private ?Produit $product = null;

    #[ORM\ManyToOne(inversedBy: 'purchase', targetEntity: Order::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idOrder', referencedColumnName: 'idOrder', nullable: false)]
    private ?Order $order = null;

    public function __construct($product, $quantity, $price)
    {
        $this->product = $product;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function getIdPurchase(): ?int
    {
        return $this->idPurchase;
    }
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getProduct(): ?Produit
    {
        return $this->product;
    }

    public function setProduct(?Produit $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function update($quantity)
    {
        $this->quantity = $quantity;
        $this->price = $this->product->getPrix() * $this->quantity;
    }

    public function getTotal()
    {
        return ($this->getProduct()->getPrix() * $this->quantity);
    }

    public function getNumberOfItems()
    {
        return $this->quantity;
    }
}
