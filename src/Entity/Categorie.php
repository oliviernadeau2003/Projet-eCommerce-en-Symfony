<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column('idCategorie')]
    private ?int $idCategorie = null;

    #[ORM\Column(length: 36)]
    private ?string $categorie = null;

    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: "categorie", fetch: "LAZY")]
    private $produits;

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function setIdCategorie(int $idCategorie): self
    {
        $this->idCategorie = $idCategorie;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function setProduit(Produit $produit): self
    {
        $this->produits = $produit;

        return $this;
    }
}
