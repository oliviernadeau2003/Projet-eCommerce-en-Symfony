<?php

namespace App\Entity;

use DateTime;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

enum States: string
{
    case InPreperation = "In Preperation";
    case Sent = "Sended";
    case InTransit = "In Transit";
    case Delivered = "Delivered";
}

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column("idOrder")]
    private ?int $idOrder = null;

    #[ORM\Column("orderDate", type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $orderDate = null;

    #[ORM\Column("deliveryDate", type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $deliveryDate = null;

    #[ORM\Column("tpsRate")]
    private ?float $tpsRate = null;

    #[ORM\Column("tvqRate")]
    private ?float $tvqRate = null;

    #[ORM\Column("shippingFee")]
    private ?float $shippingFee = null;

    #[ORM\Column(length: 255, enumType: States::class)]
    private ?States $state = null;

    #[ORM\Column("stripeIntent", length: 255)]
    private ?string $stripeIntent = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "orders", cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'idUser', nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Purchase::class, mappedBy: "order", cascade: ["persist"], fetch: "LAZY")]
    #[ORM\JoinColumn(name: 'idPurchase', referencedColumnName: 'idPurchase', nullable: false)]
    private Collection $purchase;

    // Yannick - Correction #16
    public function __construct($stripeIntent)
    {
        $this->orderDate = \DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d H:i:s"));
        $this->deliveryDate = null;
        $this->tpsRate = Constants::TPS;
        $this->tvqRate = Constants::TVQ;
        $this->shippingFee = Constants::SHIPPING_FEE;
        $this->state = States::InPreperation;
        $this->stripeIntent = $stripeIntent;
        $this->purchase = new ArrayCollection();
    }

    public function getIdOrder(): ?int
    {
        return $this->idOrder;
    }

    public function getOrderDate(): ?\DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTime $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTime
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTime $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getTpsRate(): ?float
    {
        return $this->tpsRate;
    }

    public function setTpsRate(float $tpsRate): self
    {
        $this->tpsRate = $tpsRate;

        return $this;
    }

    public function getTvqRate(): ?float
    {
        return $this->tvqRate;
    }

    public function setTvqRate(float $tvqRate): self
    {
        $this->tvqRate = $tvqRate;

        return $this;
    }

    public function getShippingFee(): ?float
    {
        return $this->shippingFee;
    }

    public function setShippingFee(float $shippingFee): self
    {
        $this->shippingFee = $shippingFee;

        return $this;
    }

    public function getState(): ?States
    {
        return $this->state;
    }

    public function setState(States $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getStripeIntent(): ?string
    {
        return $this->stripeIntent;
    }

    public function setStripeIntent(string $stripeIntent): self
    {
        $this->stripeIntent = $stripeIntent;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Purchase>
     */
    public function getPurchase(): Collection
    {
        return $this->purchase;
    }

    public function addPurchase(Purchase $purchase): self
    {
        if (!$this->purchase->contains($purchase)) {
            $this->purchase->add($purchase);
            $purchase->setOrder($this);
        }

        return $this;
    }

    public function removePurchase(Purchase $purchase): self
    {
        if ($this->purchase->removeElement($purchase)) {
            // set the owning side to null (unless already changed)
            if ($purchase->getOrder() === $this) {
                $purchase->setOrder(null);
            }
        }

        return $this;
    }

    public function getTotal()
    {
        $total = 0;
        foreach ($this->purchase as $purchase) {
            $total = $total + $purchase->getTotal();
        }
        return $total;
    }

    public function getTotalWithTaxes()
    {
        $subtotal = $this->getSubtotal();
        $tps = $subtotal * (Constants::TPS / 100);
        $tvq = $subtotal * (Constants::TVQ / 100);
        $shippingCost = Constants::SHIPPING_FEE;
        return $subtotal + $tps + $tvq + $shippingCost;
    }

    public function getNbItems()
    {
        $nbItems = 0;
        foreach ($this->purchase as $purchase) {
            $nbItems = $nbItems + $purchase->getQuantity();
        }
        return $nbItems;
    }

    public function getSubtotal()
    {
        $subtotal = 0;
        foreach ($this->purchase as $purchase) {
            $subtotal += $purchase->getPrice();
        }
        return $subtotal;
    }
}
