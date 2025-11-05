<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Home controller handling the main landing page.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Only handles home page requests
 * - Open/Closed: Extendable without modification
 * - Dependency Inversion: Depends on abstractions (AbstractController, ProjetRepository)
 */
final class HomeController extends AbstractController
{
    public function __construct(
        private readonly ProjetRepository $projetRepository
    ) {
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        // Récupération des projets avec leurs clients
        $projets = $this->projetRepository->findAllWithClients();
        $totalProjets = $this->projetRepository->countProjects();

        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur notre application Symfony',
            'message' => 'Projet créé selon les principes SOLID, DRY et KISS',
            'projets' => $projets,
            'totalProjets' => $totalProjets,
        ]);
    }
}