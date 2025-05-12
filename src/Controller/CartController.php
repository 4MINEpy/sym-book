<?php

namespace App\Controller;

use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, Request $request, SessionInterface $session, LivresRepository $livreRepository)
    {
        $quantity = $request->request->get('quantity', 1);
        $livre = $livreRepository->find($id);

        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id] += $quantity;
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', sprintf(
            '%d exemplaire(s) de "%s" ajouté(s) à votre panier',
            $quantity,
            $livre->getTitre()
        ));

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/cart', name: 'cart_index')]
    public function index(SessionInterface $session, LivresRepository $livreRepository)
    {
        $cart = $session->get('cart', []);

        $cartWithData = [];
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'livre' => $livreRepository->find($id),
                'quantity' => $quantity
            ];
        }
        $total = 0;
        foreach ($cartWithData as $item) {
            $total += $item['livre']->getPrix() * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, SessionInterface $session)
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
    #[Route('/cart/update/{id}', name: 'cart_update')]
    public function update($id, Request $request, SessionInterface $session)
    {
        $quantity = $request->request->get('quantity', 1);
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
}