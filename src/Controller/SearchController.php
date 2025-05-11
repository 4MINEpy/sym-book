<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\LivresRepository;
use Symfony\Component\HttpFoundation\Request;
final class SearchController extends AbstractController
{
    #[Route('/search', name: 'search_books')]
    public function search(Request $request, LivresRepository $livreRepository): Response
    {
        $type = $request->query->get('type', 'titre');
        $query = $request->query->get('q', '');

        // Map form type to database field
        $typeToField = [
            'titre' => 'titre',
            'auteur' => 'editeur', // "Auteur" is stored in "editeur"
            'categorie' => 'categorie.libelle',
        ];
        $field = $typeToField[$type] ?? 'titre';

        $results = $livreRepository->searchByField($field, $query);

        return $this->render('search/results.html.twig', [
            'results' => $results,
            'query' => $query,
            'type' => $type,
        ]);
    }
}
