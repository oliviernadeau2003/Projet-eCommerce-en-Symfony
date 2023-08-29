<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Order;
use App\Entity\Produit;
use App\Form\CategoryCollection;
use App\Form\CategoryCollectionType;
use App\Form\OrderType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

// #[IsGranted('ROLE_ADMIN', statusCode: 423)]
class AdminController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/management', name: 'app_admin_management')]
    public function index(): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');



        return $this->render('admin_controller/index.html.twig', []);
    }

    #[Route('/admin/management/categories', name: 'app_admin_categories')]
    public function categories(Request $request): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');

        $categories = $this->em->getRepository(Categorie::class)->findAll();
        $categoriesCollection = new CategoryCollection($categories);

        $form = $this->createForm(CategoryCollectionType::class, $categoriesCollection);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newCategoriesCollection = $form->getData()->getCategories();

            foreach ($newCategoriesCollection as $newCategory) {
                $this->em->persist($newCategory);
            }
            $this->em->flush();
        }

        return $this->render('admin_controller/categories.html.twig', [
            "categoriesForm" => $form
        ]);
    }

    #[Route('/admin/management/products', name: 'app_admin_products')]
    public function products(): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');

        $products = $this->em->getRepository(Produit::class)->findAll();

        return $this->render('admin_controller/products.html.twig', [
            "products" => $products
        ]);
    }

    #[Route('/admin/management/product/{idProduct}', name: 'app_admin_product')]
    public function product($idProduct, Request $request, SluggerInterface $slugger): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            return $this->redirectToRoute('app_catalogue');

        $product = $this->em->getRepository(Produit::class)->find($idProduct);
        if (!$product)
            return $this->redirectToRoute('app_admin_products');

        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $newProduct = $productForm->getData();

            $profilePicture = $productForm->get('imagePath')->getData();
            if ($profilePicture) {
                $originalFilename = pathinfo($profilePicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $profilePicture->guessExtension();

                try {
                    $profilePicture->move(
                        $this->getParameter('profile_picture_directory'),
                        $newFilename
                    );

                    $newProduct->setImagePath("/images/profiles/" . $newFilename);
                } catch (FileException $e) {
                    //TODO: Erreur
                } catch (ORMException $e) {
                    //TODO: Erreur
                }
            } else
                $newProduct->setImagePath("noImageUploaded");

            $this->em->persist($newProduct);
            $this->em->flush();
            return $this->redirectToRoute('app_admin_product', ["idProduct" => $product->getIdProduit()]);
        }

        return $this->render('admin_controller/product.html.twig', [
            "product" => $product, "productForm" => $productForm
        ]);
    }

    #[Route('/admin/management/new/product', name: 'app_admin_new_product')]
    public function newProduct(Request $request, SluggerInterface $slugger): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');

        $product = new Produit();
        $productForm = $this->createForm(ProductType::class, $product);
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $newProduct = $productForm->getData();

            $profilePicture = $productForm->get('imagePath')->getData();
            if ($profilePicture) {
                $originalFilename = pathinfo($profilePicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $profilePicture->guessExtension();

                try {
                    $profilePicture->move(
                        $this->getParameter('profile_picture_directory'),
                        $newFilename
                    );

                    $newProduct->setImagePath("/images/profiles/" . $newFilename);
                } catch (FileException $e) {
                    //TODO: Erreur
                } catch (ORMException $e) {
                    //TODO: Erreur
                }
            } else
                $newProduct->setImagePath("noImageUploaded");

            $this->em->persist($newProduct);
            $this->em->flush();
            return $this->redirectToRoute('app_admin_product', ["idProduct" => $product->getIdProduit()]);
        }

        return $this->render('admin_controller/new.product.html.twig', [
            "product" => $product, "productForm" => $productForm
        ]);
    }

    #[Route('/admin/management/orders', name: 'app_admin_orders')]
    public function orders(): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');

        $orders = $this->em->getRepository(Order::class)->findAll(["orderDate" => 'DESC']);

        return $this->render('admin_controller/orders.html.twig', [
            "orders" => $orders
        ]);
    }

    #[Route('/admin/management/order/{idOrder}', name: 'app_admin_order')]
    public function order($idOrder, Request $request): Response
    {
        // Denied Access to non-admin user
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN'))
            $this->redirectToRoute('app_catalogue');

        $order = $this->em->getRepository(Order::class)->find($idOrder);
        $orderForm = $this->createForm(OrderType::class, $order);
        $orderForm->handleRequest($request);
        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $updatedOrder = $orderForm->getData();
            $this->em->persist($updatedOrder);
            $this->em->flush();
        }

        return $this->render('admin_controller/order.html.twig', [
            "order" => $order, "orderForm" => $orderForm
        ]);
    }

    // #[Route('/admin/management/order/submit', name: 'app_admin_order_submit', methods: "POST")]
    // public function HandleOrderFormSubmition(Request $request)
    // {
    // }
}
