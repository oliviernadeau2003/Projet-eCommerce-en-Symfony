<?php

namespace App\Controller;

use Exception;
use App\Entity\Cart;
use App\Core\Notification;
use App\Core\NotificationColor;
use App\Entity\Categorie;
use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Length;

use function PHPUnit\Framework\throwException;

class StripeController extends AbstractController
{

    private $em = null;
    private $cart;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    #[Route('/stripe-checkout', name: 'stripe-checkout')]
    public function stripeCheckout(Request $request): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $this->initSession($request);
        $session = $request->getSession();
        $cart = $session->get('cart');

        \Stripe\Stripe::setApiKey($_ENV["STRIPE_KEY"]);

        $successURL = $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . "?stripe_id={CHECKOUT_SESSION_ID}";

        $sessionData = [
            'line_items' => [[
                'quantity' => 1,
                'price_data' => ['unit_amount' => $cart->getStripeTotal(), 'currency' => 'CAD', 'product_data' => ['name' => 'Wrist Luxury']]
            ]],
            'customer_email' => $user->getEmail(),
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $successURL,
            'cancel_url' => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];


        $checkoutSession = \Stripe\Checkout\Session::create($sessionData);
        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/stripe-success', name: 'stripe_success')]
    public function stripeSuccess(Request $request): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $this->initSession($request);
        $session = $request->getSession();
        $cart = $session->get('cart');

        try {

            $stripe = new \Stripe\StripeClient($_ENV["STRIPE_KEY"]);

            $stripeSessionId = $request->query->get('stripe_id');
            $sessionStripe = $stripe->checkout->sessions->retrieve($stripeSessionId);
            $paymentIntent = $sessionStripe->payment_intent;

            if ($stripeSessionId != $sessionStripe->id)
                throw new Exception('Stripe Token Error');

            //Dans le TP 
            //Créer un commande
            $order = new Order($paymentIntent);
            $order->setUser($user);
            //Transformer le panier en commande
            $outOfStockProducts = [];
            foreach ($cart->getPurchases() as $purchase) {
                $order->addPurchase($purchase);
                //Dans le TP ICI:
                //Il faudra appeler la méthode merge de l'entité manager sur chaque achat
                $purchase = $this->em->merge($purchase);
                //MaJ des Quantité des produits
                $newProductQuantity = ($purchase->getProduct()->getQuantiteEnStock() - $purchase->getQuantity());
                $purchase->getProduct()->setQuantiteEnStock($newProductQuantity);

                if ($purchase->getProduct()->isOutOfStock())
                    array_push($outOfStockProducts, $purchase->getProduct());
            }

            if ($outOfStockProducts) {
                $stringBuilder = "";
                foreach ($outOfStockProducts as $productOutOfStock) {
                    $stringBuilder = $stringBuilder . "The product <strong>" . $productOutOfStock->getNom() . "</strong> is out of stock<br>";
                }
                $stringBuilder = $stringBuilder . "Your order <strong>#" . $order->getIdOrder() . "</strong> will be delivered as soon as the missing products are available";
                $this->addFlash(
                    'productOutOfStock',
                    new Notification('info', $stringBuilder, NotificationColor::WARNING)
                );
            }

            //?     TEST
            // $idCategorie = $order->getPurchase()[0]->getProduct()->getCategorie()->getIdCategorie();
            // $categorie = $this->em->getRepository(Categorie::class)->find($idCategorie);
            // $order->getPurchase()[0]->getProduct()->getCategorie()->setCategorie($categorie->getCategorie());

            // dd($order);

            // $this->em->persist($order);      //! DOESNT WORK
            // $this->em->flush();

            //Vider le panier
            $cart->empty();
        } catch (\Exception $e) {
            // dd($e, $order->getPurchase()[0]->getProduct());
            $this->addFlash(
                'cart',
                new Notification('error', 'An unexpected error has occurred, please try again later', NotificationColor::DANGER)
            );
            return $this->redirectToRoute('app_cart');
        }

        return $this->redirectToRoute('app_order_details', ["idOrder" => $order->getIdOrder() || 1]);   // FIXME: idOrder est null donc on retourne 1 pour ne pas bloque le site
    }

    #[Route('/stripe-cancel', name: 'stripe_cancel')]
    public function stripeCancel(): Response
    {
        $this->addFlash(
            'cart',
            new Notification('error', 'Unable to make payment. Try Again', NotificationColor::DANGER)
        );
        return $this->redirectToRoute('app_cart');
    }

    // ---

    private function initSession(Request $request)
    {
        $session = $request->getSession();
        $this->cart = $session->get('cart', new Cart());

        $session->set('cart', $this->cart);
    }
}
