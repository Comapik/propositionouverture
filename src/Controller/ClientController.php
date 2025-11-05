<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Client controller handling CRUD operations.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Manages client operations only
 * - Open/Closed: Extendable without modification
 * - Dependency Inversion: Depends on abstractions
 */
#[Route('/clients')]
final class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(): Response
    {
        $clients = $this->clientRepository->findBy([], ['nom' => 'ASC']);

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/nouveau', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été créé avec succès.');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_client_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été modifié avec succès.');

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'app_client_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->getPayload()->getString('_token'))) {
            // Vérifier si le client a des projets associés
            if ($client->getProjets()->count() > 0) {
                $this->addFlash('error', 'Impossible de supprimer ce client car il a des projets associés.');
            } else {
                $this->entityManager->remove($client);
                $this->entityManager->flush();
                $this->addFlash('success', 'Le client a été supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('app_client_index');
    }
}