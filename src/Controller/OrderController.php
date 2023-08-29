<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Constants;
use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    private $em = null;
    private $cart;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/order/review', name: 'app_order_review')]
    public function orderReview(Request $request): Response
    {
        $user = $this->getUser();

        $this->initSession($request);
        $session = $request->getSession();
        $cart = $session->get('cart');

        if ($user == null || $cart->isEmpty())
            return $this->redirectToRoute('app_authentication');

        //! Temporaire TP ES - Correction des erreus de POO
        $subtotal = $this->getSubtotal($cart);
        $tps = $subtotal * (Constants::TPS / 100);
        $tvq = $subtotal * (Constants::TVQ / 100);
        if ($subtotal > 0)
            $shippingCost = Constants::SHIPPING_FEE;
        else
            $shippingCost = 0;
        $total = $subtotal + $tps + $tvq + $shippingCost;
        //! ---


        return $this->render('order/orderReview.html.twig', [
            "purchases" => $cart->getPurchases(),
            //! ---
            "subtotal" => $subtotal,
            "tps" => $tps,
            "tvq" => $tvq,
            "shippingCost" => $shippingCost,
            "total" => $total,
            'cart' => $cart
            //! ---
        ]);
    }

    #[Route('/order/{idOrder}', name: 'app_order_details')]
    public function orders_details($idOrder): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_authentication');

        $order = $this->em->getRepository(Order::class)->findOneBy(["idOrder" => $idOrder, "user" => $user], ["orderDate" => 'ASC']);
        if (!$order) {
            return $this->redirectToRoute('app_orders');
        }

        return $this->render('order/orderDetails.html.twig', [
            "order" => $order
        ]);
    }

    #[Route('/orders', name: 'app_orders')]
    public function orders(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user)
            return $this->redirectToRoute('app_authentication');

        $orders = $this->em->getRepository(Order::class)->findBy(["user" => $user], ["orderDate" => 'ASC']);

        return $this->render('order/orders.html.twig', [
            "orders" => $orders
        ]);
    }

    // ---

    private function initSession(Request $request)
    {
        $session = $request->getSession();
        $this->cart = $session->get('cart', new Cart());

        $session->set('cart', $this->cart);
    }

    //! Temporaire
    private function getSubtotal($cart)
    {
        $subtotal = 0;
        foreach ($cart->getPurchases() as $purchase) {
            $subtotal += $purchase->getPrice();
        }
        return $subtotal;
    }
}
