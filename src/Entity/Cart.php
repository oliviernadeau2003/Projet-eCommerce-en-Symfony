<?php

namespace App\Entity;

use App\Entity\Purchase;
use Doctrine\ORM\Query\Expr\Math;

class Cart
{
    private $purchases = [];

    public function add(Produit $product, $quantity, $price)
    {
        $purchase = new Purchase($product, $quantity, $price);
        $this->purchases[] = $purchase;
    }

    public function update($purchases)
    {
        if (count($this->purchases) > 0) {
            foreach ($this->purchases as $key => $purchase) {
                $purchase->update($purchases[$key]);
                if ($purchases[$key] < 1)
                    unset($this->purchases[$key]);
            }
        }
    }

    public function delete($index)
    {
        if (array_key_exists($index, $this->purchases)) {
            unset($this->purchases[$index]);
        }
    }

    public function getPurchases()
    {
        return $this->purchases;
    }

    public function updateExistingPurchase($idProduct)
    {
        foreach ($this->purchases as $purchase)
            if ($purchase->getProduct()->getIdProduit() == $idProduct)
                $purchase->update($purchase->getQuantity() + 1);
    }

    public function empty()
    {
        return $this->purchases = array();
    }

    public function getStripeTotal()
    {
        return round($this->getTotal() * 100);
    }

    public function getTotal()
    {
        $subtotal = $this->getSubTotal();
        $tps = $this->getTPS($subtotal);
        $tvq = $this->getTVQ($subtotal);
        if ($subtotal > 0)
            $shippingCost = Constants::SHIPPING_FEE;
        else
            $shippingCost = 0;

        return $subtotal + $tps + $tvq + $shippingCost;
    }

    public function getSubTotal()
    {
        $subtotal = 0;
        foreach ($this->getPurchases() as $purchase)
            $subtotal += $purchase->getPrice();

        return $subtotal;
    }

    public function getTPS($subtotal)
    {
        return $subtotal * (Constants::TPS / 100);
    }

    public function getTVQ($subtotal)
    {
        return $subtotal * (Constants::TVQ / 100);
    }

    public function isEmpty()
    {
        if (count($this->purchases) <= 0)
            return true;
        else
            return false;
    }

    public function addProduct($product)
    {
        if ($this->isNewProduct($product->getIdProduit())) {
            $this->add($product, 1, $product->getPrix());
        } else {
            $this->updateExistingPurchase($product->getIdProduit());
        }
    }

    public function isNewProduct($idProduct): Bool
    {
        $isNew = true;
        foreach ($this->purchases as $product)
            if ($product->getProduct()->getIdProduit() == $idProduct)
                $isNew = false;
        return $isNew;
    }
}
