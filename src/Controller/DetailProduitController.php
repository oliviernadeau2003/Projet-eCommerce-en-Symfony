<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailProduitController extends AbstractController
{

    private $em = null;

    #[Route('/produit/{idProduit}', name: 'app_detail_produit')]
    public function index($idProduit, ManagerRegistry $doctrine): Response
    {

        $this->em = $doctrine->getManager();
        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        return $this->render('detail_produit/index.html.twig', ['produit' => $produit]);
    }
}
