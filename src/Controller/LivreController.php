<?php

namespace App\Controller;


use App\Entity\Categories;
use App\Entity\Livres;
use App\Form\LivreType;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LivreController extends AbstractController
{
    #[Route('admin/livre/show3', name: 'livre_show3')]
    public function show3(LivresRepository $rep): Response
    {
        $livres = $rep->findBy(['editeur'=>'enit'],['prix'=>'DESC']);
        if(!$livres){
            {throw $this->createNotFoundException("Livre n'existe pas");}
        }
        dd($livres);
    }
    #[Route('admin/livre/show2', name: 'livre_show2')]
    public function show2(LivresRepository $rep): Response
    {
        $livres = $rep->findOneBy(['titre'=>'titre 1','editeur'=>'editeur 1']);
        if(!$livres){
            {throw $this->createNotFoundException("Livre n'existe pas");}
        }
        dd($livres);
    }

    #[Route('admin/livre/show/{id}', name: 'livre_show')]
    public function show(Livres $livre): Response
    {
        return $this->render('livre/detail.html.twig', ['livre'=>$livre]);
    }

    #[Route('admin/livre/create', name: 'create_livre')]
    public function create(Request $request,EntityManagerInterface $manager): Response
    {
        $livre = new livres();
        //Afficher le formulaire
        $form = $this->createForm(LivreType::class, $livre);
        //traitement des données issues
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($categorie);
            $manager->persist($livre);
            $manager->flush();
            $this->addFlash('success','le livre a été bien ajouté');
            return $this->redirectToRoute('livre_liste');
        }

        return $this->render('livre/create.html.twig', ['f' => $form]);
    }

    #[Route('admin/livre/delete/{id}', name: 'livre_delete')]
    public function delete(EntityManagerInterface $em,Livres $livres): Response
    {

        $em->remove($livres);
        $em->flush();
        return $this->redirectToRoute('livre_liste');
    }
    #[Route('admin/livre/update/{id}', name: 'livre_update')]
    public function update(EntityManagerInterface $em,Livres $livres): Response
    {

        $newPrix = $livres->getPrix()*1.1;
        $livres->setPrix($newPrix);
        $em->persist($livres);
        $em->flush();
        dd($livres);
    }
    #[Route('admin/livre/liste', name: 'livre_liste')]
    public function liste(LivresRepository $rep, PaginatorInterface $paginator, Request $request): Response
    {

        $livres = $paginator->paginate(
            $rep->findAll(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('livre/all.html.twig', ['livres' => $livres]);
    }
    #[Route('detail/{id}', name: 'detail_livre')]
    public function detail(Livres $livres, Request $request): Response
    {
        return $this->render('livre/detail_user.html.twig', ['livres'=>$livres]);
    }
}
