<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        LivresRepository $lrep,
        CategoriesRepository $crep,
        Request $request
    ): Response {
        // Get sort parameter from request
        $sort = $request->query->get('sort', 'default');

        // Fetch books based on sort parameter
        switch ($sort) {
            case 'recent':
                $livres = $lrep->findBy([], ['dateEdition' => 'DESC']);
                break;
            case 'price_asc':
                $livres = $lrep->findBy([], ['prix' => 'ASC']);
                break;
            case 'price_desc':
                $livres = $lrep->findBy([], ['prix' => 'DESC']);
                break;
            case 'bestsellers':
                // You'll need to implement this logic based on your sales data
                $livres = $lrep->findAll();
                break;
            default:
                $livres = $lrep->findAll();
        }

        $categories = $crep->findAll();

        return $this->render('home/home.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'current_sort' => $sort
        ]);
    }
}