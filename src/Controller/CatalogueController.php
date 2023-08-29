<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{

    private $em = null;

    #[Route('/', name: 'app_catalogue')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->em = $doctrine->getManager();

        $categorie = $request->query->get('categorie');
        $searchField = $request->request->get('search_field');

        $produits = $this->retrieveProduits($categorie, $searchField);
        $categories = $this->retrieveAllCategories();

        return $this->render('catalogue/catalogue.html.twig', ['produits' => $produits, 'categories' => $categories]);
    }

    // Query Function ---

    private function retrieveProduits($categorie, $searchField)
    {
        return $this->em->getRepository(Produit::class)->findWithCriteria($categorie, $searchField);
    }

    private function retrieveAllCategories()
    {
        return $this->em->getRepository(Categorie::class)->findAll();
    }


    // private function initSession(Request $request)       //!! ------------------------
    // {
    //     $session = $request->getSession();
    //     $session->set('name', 'Olivier');
    // }
}
