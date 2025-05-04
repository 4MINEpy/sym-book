<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategorieType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategorieController extends AbstractController
{
    #[Route('admin/categorie', name: 'admin_categorie')]
    public function index(CategoriesRepository $rep): Response
    {
        $categories = $rep->findAll();
        return $this->render('categorie/index.html.twig',['categories' => $categories]
        );
    }

    #[Route('admin/categorie/create', name: 'create_categorie')]
    public function create(Request $request,EntityManagerInterface $manager): Response
    {
        $categorie = new Categories();
        //Afficher le formulaire
        $form = $this->createForm(CategorieType::class, $categorie);
        //traitement des données issues
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($categorie);
            $manager->persist($categorie);
            $manager->flush();
            $this->addFlash('success','la catégorie a été bien ajoutée');
            return $this->redirectToRoute('admin_categorie');
        }

        return $this->render('categorie/create.html.twig', ['f' => $form]);
    }

    #[Route('admin/categorie/update/{id}', name: 'update_categorie')]
    public function update(Request $request,EntityManagerInterface $manager,Categories $categorie): Response
    {

        //Afficher le formulaire
        $form = $this->createForm(CategorieType::class, $categorie);
        //traitement des données issues
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($categorie);
            $manager->persist($categorie);
            $manager->flush();
            $this->addFlash('success','la catégorie a été bien editée');
            return $this->redirectToRoute('admin_categorie');
        }

        return $this->render('categorie/update.html.twig', ['f' => $form]);
    }
}
