<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\LivresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(LivresRepository $lrep, CategoriesRepository $crep): Response
    {
        $livres = $lrep->findAll();
        $categories = $crep->findAll();

        return $this->render('home/home.html.twig',['livres'=>$livres,'categories'=>$categories]);
    }
}
