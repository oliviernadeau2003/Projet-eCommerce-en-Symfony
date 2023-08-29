<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column('idProduit')]
    private ?int $idProduit = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column('quantiteEnStock')]
    private ?int $quantiteEnStock = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\Column(name: 'imagePath', length: 255)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'idCategorie', referencedColumnName: 'idCategorie')]
    private $categorie;

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantiteEnStock(): ?int
    {
        return $this->quantiteEnStock;
    }

    public function setQuantiteEnStock(int $quantiteEnStock): self
    {
        $this->quantiteEnStock = $quantiteEnStock;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function isOutOfStock()
    {
        if ($this->quantiteEnStock > 0)
            return false;
        else
            return true;
    }
}
