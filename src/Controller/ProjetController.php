<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Projet controller handling CRUD operations.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Manages project operations only
 * - Open/Closed: Extendable without modification
 * - Dependency Inversion: Depends on abstractions
 */
#[Route('/projets')]
final class ProjetController extends AbstractController
{
    public function __construct(
        private readonly ProjetRepository $projetRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_projet_index', methods: ['GET'])]
    public function index(): Response
    {
        $projets = $this->projetRepository->findAllWithClients();

        return $this->render('projet/index.html.twig', [
            'projets' => $projets,
        ]);
    }

    #[Route('/nouveau', name: 'app_projet_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si un nouveau client doit être créé
            $clientChoice = $form->get('clientChoice')->getData();
            
            if ($clientChoice === 'new') {
                $newClientData = $form->get('newClient')->getData();
                if ($newClientData && $newClientData->getNom()) {
                    // Créer et persister le nouveau client
                    $this->entityManager->persist($newClientData);
                    $projet->setClient($newClientData);
                }
            }
            
            $this->entityManager->persist($projet);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le projet a été créé avec succès. Choisissez maintenant le type de configuration.');

            // Rediriger vers la page de choix de configuration
            return $this->redirectToRoute('app_projet_choose_configuration', ['projet' => $projet->getId()]);
        }

        return $this->render('projet/new.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/choose-configuration/{projet}', name: 'app_projet_choose_configuration', methods: ['GET', 'POST'])]
    public function chooseConfiguration(Projet $projet, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $configType = $request->request->get('config_type');
            
            switch ($configType) {
                case 'conf_pf':
                    return $this->redirectToRoute('app_configuration_pf', [
                        'projet' => $projet->getId(),
                    ]);
                    
                case 'conf_volet':
                    return $this->redirectToRoute('app_configuration_volet', [
                        'projet' => $projet->getId(),
                    ]);
                    
                case 'skip':
                    $this->addFlash('info', 'Configuration ignorée. Vous pourrez la faire plus tard depuis la fiche projet.');
                    return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
                    
                default:
                    $this->addFlash('warning', 'Veuillez choisir un type de configuration.');
                    break;
            }
        }
        
        return $this->render('projet/choose_config.html.twig', [
            'projet' => $projet,
        ]);
    }

    #[Route('/configure-pf/{projet}', name: 'app_projet_configure_pf', methods: ['GET'])]
    public function configurePf(Projet $projet): Response
    {
        return $this->redirectToRoute('app_configuration_pf', [
            'projet' => $projet->getId(),
        ]);
    }

    #[Route('/configure-volet/{projet}', name: 'app_projet_configure_volet', methods: ['GET'])]
    public function configureVolet(Projet $projet): Response
    {
        return $this->redirectToRoute('app_configuration_volet', [
            'projet' => $projet->getId(),
        ]);
    }

    #[Route('/{id}/configurer-fenetre-porte', name: 'app_projet_config_pf', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function configurePorteFenetre(Request $request, Projet $projet): Response
    {
        // TODO: Implémenter la configuration des portes/fenêtres
        $this->addFlash('info', 'Configuration des portes/fenêtres - À implémenter');
        return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
    }



    #[Route('/{id}', name: 'app_projet_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Projet $projet): Response
    {
        // Récupérer la configuration ConfPf si elle existe
        $confPf = null;
        if ($projet->getConfPfId()) {
            $confPf = $this->entityManager->getRepository(\App\Entity\ConfPf::class)
                ->find($projet->getConfPfId());
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'confPf' => $confPf,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_projet_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Projet $projet): Response
    {
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le projet a été modifié avec succès.');

            return $this->redirectToRoute('app_projet_index');
        }

        return $this->render('projet/edit.html.twig', [
            'projet' => $projet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_projet_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($projet);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le projet a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_projet_index');
    }
}