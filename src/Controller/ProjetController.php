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
        // Récupérer les photos du projet
        $photos = [];
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId();
        $metadataFile = $photosDir . '/metadata.json';
        
        if (file_exists($metadataFile)) {
            $metadataContent = file_get_contents($metadataFile);
            $allMetadata = json_decode($metadataContent, true) ?? [];
            
            foreach ($allMetadata as $filename => $metadata) {
                $filePath = $photosDir . '/' . $filename;
                if (file_exists($filePath)) {
                    $photos[] = [
                        'filename' => $filename,
                        'title' => $metadata['title'] ?? 'Photo',
                        'originalName' => $metadata['originalName'] ?? $filename,
                        'size' => $metadata['size'] ?? filesize($filePath),
                        'uploadDate' => isset($metadata['uploadDate']) ? new \DateTime($metadata['uploadDate']) : null,
                        'path' => $this->generateUrl('app_serve_photo', [
                            'projetId' => $projet->getId(),
                            'filename' => $filename
                        ])
                    ];
                }
            }
        }
        
        // Récupérer la configuration ConfPf si elle existe
        $confPf = null;
        $generatedPdfs = [];
        if ($projet->getConfPfId()) {
            try {
                $confPf = $this->entityManager->getRepository(\App\Entity\ConfPf::class)
                    ->find($projet->getConfPfId());
                    
                // Récupérer les PDFs générés depuis la base de données
                if ($confPf) {
                    $projetPdfs = $this->entityManager->getRepository(\App\Entity\ProjetPdf::class)
                        ->findByProjetOrderedByDate($projet);
                    
                    foreach ($projetPdfs as $projetPdf) {
                        // Vérifier que le fichier existe encore
                        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $projetPdf->getFilePath();
                        if (file_exists($fullPath)) {
                            $generatedPdfs[] = [
                                'id' => $projetPdf->getId(),
                                'filename' => $projetPdf->getFileName(),
                                'path' => $projetPdf->getFilePath(),
                                'encodedPath' => $projetPdf->getEncodedPath(),
                                'size' => $projetPdf->getFileSize(),
                                'customValue' => $projetPdf->getCustomValue(),
                                'created' => $projetPdf->getCreatedAt()->getTimestamp(),
                                'createdAt' => $projetPdf->getCreatedAt(),
                                'formattedSize' => $projetPdf->getFormattedSize(),
                            ];
                        }
                    }
                }
                    
                // Pré-charger toutes les entités liées pour détecter les erreurs
                if ($confPf) {
                    try {
                        // Forcer le chargement de toutes les entités liées pour détecter les erreurs
                        if ($confPf->getSysteme()) {
                            $confPf->getSysteme()->getNom(); // Force le chargement
                        }
                        if ($confPf->getFournisseur()) {
                            $confPf->getFournisseur()->getMarque(); // Force le chargement
                        }
                        if ($confPf->getCouleurInterieur()) {
                            $confPf->getCouleurInterieur()->getNom(); // Force le chargement
                        }
                        if ($confPf->getCouleurExterieur()) {
                            $confPf->getCouleurExterieur()->getNom(); // Force le chargement
                        }
                        if ($confPf->getTypeFenetrePorte()) {
                            $confPf->getTypeFenetrePorte()->getNom(); // Force le chargement
                        }
                        if ($confPf->getOuverture()) {
                            $confPf->getOuverture()->getNom(); // Force le chargement
                        }
                        if ($confPf->getVitrage()) {
                            $confPf->getVitrage()->getType(); // Force le chargement
                        }
                        if ($confPf->getConfAeration()) {
                            $confPf->getConfAeration()->getAeration()->getModele(); // Force le chargement
                            $confPf->getConfAeration()->getPosition()->getPosition(); // Force le chargement
                        }
                    } catch (\Doctrine\ORM\EntityNotFoundException $e) {
                        // Une entité liée n'existe plus, nettoyer la configuration
                        $this->cleanOrphanedReferences($confPf);
                        $this->addFlash('warning', 'Certaines références de cette configuration n\'existaient plus et ont été nettoyées automatiquement.');
                    }
                }
            } catch (\Exception $e) {
                // Erreur lors du chargement de la configuration
                $this->addFlash('error', 'Erreur lors du chargement de la configuration du projet.');
                $confPf = null;
            }
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'confPf' => $confPf,
            'photos' => $photos,
            'generatedPdfs' => $generatedPdfs,
        ]);
    }

    #[Route('/{id}/pdf/{pdfId}/delete', name: 'app_projet_delete_pdf', methods: ['POST'], requirements: ['id' => '\d+', 'pdfId' => '\d+'])]
    public function deletePdf(Projet $projet, int $pdfId, Request $request): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete_pdf_' . $pdfId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        // Récupérer le PDF
        $projetPdf = $this->entityManager->getRepository(\App\Entity\ProjetPdf::class)->find($pdfId);
        
        if (!$projetPdf) {
            $this->addFlash('error', 'PDF introuvable.');
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }
        
        // Vérifier que le PDF appartient bien au projet
        if ($projetPdf->getProjet()->getId() !== $projet->getId()) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        try {
            // Supprimer le fichier physique
            $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $projetPdf->getFilePath();
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Supprimer l'enregistrement de la base de données
            $this->entityManager->remove($projetPdf);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'PDF "' . $projetPdf->getFileName() . '" supprimé avec succès.');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du PDF : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
    }

    /**
     * Nettoie les références orphelines d'une configuration
     */
    private function cleanOrphanedReferences(\App\Entity\ConfPf $confPf): void
    {
        $needsFlush = false;
        
        // Nettoyer les références système
        if ($confPf->getSysteme()) {
            try {
                $systeme = $this->entityManager->getRepository(\App\Entity\Systeme::class)
                    ->find($confPf->getSysteme()->getId());
                if (!$systeme) {
                    $confPf->setSysteme(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setSysteme(null);
                $needsFlush = true;
            }
        }
        
        // Nettoyer les références fournisseur
        if ($confPf->getFournisseur()) {
            try {
                $fournisseur = $this->entityManager->getRepository(\App\Entity\Fournisseur::class)
                    ->find($confPf->getFournisseur()->getId());
                if (!$fournisseur) {
                    $confPf->setFournisseur(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setFournisseur(null);
                $needsFlush = true;
            }
        }
        
        // Nettoyer les références couleurs
        if ($confPf->getCouleurInterieur()) {
            try {
                $couleur = $this->entityManager->getRepository(\App\Entity\Couleur::class)
                    ->find($confPf->getCouleurInterieur()->getId());
                if (!$couleur) {
                    $confPf->setCouleurInterieur(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setCouleurInterieur(null);
                $needsFlush = true;
            }
        }
        
        if ($confPf->getCouleurExterieur()) {
            try {
                $couleur = $this->entityManager->getRepository(\App\Entity\Couleur::class)
                    ->find($confPf->getCouleurExterieur()->getId());
                if (!$couleur) {
                    $confPf->setCouleurExterieur(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setCouleurExterieur(null);
                $needsFlush = true;
            }
        }
        
        // Nettoyer les autres références
        if ($confPf->getTypeFenetrePorte()) {
            try {
                $type = $this->entityManager->getRepository(\App\Entity\TypeFenetrePorte::class)
                    ->find($confPf->getTypeFenetrePorte()->getId());
                if (!$type) {
                    $confPf->setTypeFenetrePorte(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setTypeFenetrePorte(null);
                $needsFlush = true;
            }
        }
        
        if ($confPf->getOuverture()) {
            try {
                $ouverture = $this->entityManager->getRepository(\App\Entity\Ouverture::class)
                    ->find($confPf->getOuverture()->getId());
                if (!$ouverture) {
                    $confPf->setOuverture(null);
                    $needsFlush = true;
                }
            } catch (\Exception $e) {
                $confPf->setOuverture(null);
                $needsFlush = true;
            }
        }
        
        if ($needsFlush) {
            $this->entityManager->flush();
        }
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
            try {
                // Supprimer d'abord toutes les configurations liées au projet
                
                // Supprimer les configurations ConfPf liées
                if ($projet->getConfPfId()) {
                    $confPf = $this->entityManager->getRepository(\App\Entity\ConfPf::class)
                        ->find($projet->getConfPfId());
                    if ($confPf) {
                        $this->entityManager->remove($confPf);
                    }
                }
                
                // Supprimer les configurations ConfVolet liées
                // if ($projet->getConfVoletId()) {
                //     $confVolet = $this->entityManager->getRepository(\App\Entity\ConfVolet::class)
                //         ->find($projet->getConfVoletId());
                //     if ($confVolet) {
                //         $this->entityManager->remove($confVolet);
                //     }
                // }
                
                // Supprimer toutes les autres configurations qui pourraient référencer ce projet
                $confPfs = $this->entityManager->getRepository(\App\Entity\ConfPf::class)
                    ->findBy(['projet' => $projet]);
                foreach ($confPfs as $confPf) {
                    $this->entityManager->remove($confPf);
                }
                
                // Vérifier s'il y a une entité ConfVolet
                // try {
                //     $confVolets = $this->entityManager->getRepository(\App\Entity\ConfVolet::class)
                //         ->findBy(['projet' => $projet]);
                //     foreach ($confVolets as $confVolet) {
                //         $this->entityManager->remove($confVolet);
                //     }
                // } catch (\Exception $e) {
                //     // L'entité ConfVolet n'existe peut-être pas encore
                // }
                
                // Maintenant supprimer le projet lui-même
                $this->entityManager->remove($projet);
                $this->entityManager->flush();

                $this->addFlash('success', 'Le projet et toutes ses configurations ont été supprimés avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression du projet : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_projet_index');
    }
}