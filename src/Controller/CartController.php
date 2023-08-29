<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Produit;
use App\Entity\Constants;
use App\Core\Notification;
use App\Core\NotificationColor;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    private $cart;

    #[Route('/cart', name: 'app_cart')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->initSession($request);
        $session = $request->getSession();
        $cart = $session->get('cart');

        //! To Change TP05 - POO
        // $subtotal = $this->getSubtotal($cart);
        // $tps = $subtotal * (Constants::TPS / 100);
        // $tvq = $subtotal * (Constants::TVQ / 100);
        // if ($subtotal > 0)
        //     $shippingCost = Constants::SHIPPING_FEE;
        // else
        //     $shippingCost = 0;
        // $total = $subtotal + $tps + $tvq + $shippingCost;
        $subtotal = $cart->getSubtotal();
        return $this->render('cart/cart.html.twig', [
            "subtotal" => $cart->getSubtotal(),
            "tps" => $cart->getTPS($subtotal),
            "tvq" => $cart->getTVQ($subtotal),
            "shippingCost" => Constants::SHIPPING_FEE,
            "total" => $cart->getTotal(),
            'cart' => $cart
        ]);
    }

    #[Route('/cart/add/{idProduct}', name: 'app_cart_add_product')]
    public function addPurchase($idProduct, ManagerRegistry $doctrine, Request $request): Response
    {
        $this->initSession($request);
        $session = $request->getSession();
        $cart = $session->get('cart');

        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Produit::class)->find($idProduct);

        $cart->addProduct($product);
        if ($cart->isNewProduct($idProduct)) {
            $this->addFlash(
                'cart',
                new Notification('success', 'The product has been added to your cart', NotificationColor::INFO)
            );
        } else {
            $this->addFlash(
                'cart',
                new Notification('success', 'Your cart has been updated', NotificationColor::INFO)
            );
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update', name: 'update_cart', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $post = $request->request->all();
        $this->initSession($request);

        $action = $request->request->get('action');

        if ($action == "update") {
            $this->cart->update($post['inputPurchaseQuantity']);
            $this->addFlash(
                'cart',
                new Notification('success', 'Your cart has been updated', NotificationColor::INFO)
            );
        } else if ($action == "empty") {
            $session = $request->getSession();
            $session->remove('cart');
            // $this->addFlash(
            //     'cart',
            //     new Notification('success', 'Your cart has been clear', NotificationColor::DANGER)
            // );
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delete/{index}', name: 'app_cart_delete_one')]
    public function deletePurchase($index, Request $request): Response
    {
        $this->initSession($request);
        $this->cart->delete($index);

        $this->addFlash(
            'cart',
            new Notification('success', 'The product has been removed from your cart', NotificationColor::INFO)
        );

        return $this->redirectToRoute('app_cart');
    }

    // -------------------

    private function initSession(Request $request)
    {
        $session = $request->getSession();
        $this->cart = $session->get('cart', new Cart());

        $session->set('cart', $this->cart);
    }

    //! Temporaire -- TP05
    private function getSubtotal($cart)
    {
        $subtotal = 0;
        foreach ($cart->getPurchases() as $purchase) {
            $subtotal += $purchase->getPrice();
        }
        return $subtotal;
    }
}
