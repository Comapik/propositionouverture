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
        $confVolet = null;
        $confTeinteTablier = null;
        $lignesCommandeVolet = [];
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

        // Récupérer la configuration ConfVolet si elle existe
        if ($projet->getConfVoletId()) {
            try {
                $connection = $this->entityManager->getConnection();

                $confVolet = $connection->fetchAssociative(
                    'SELECT cv.*, 
                            gv.nom AS gamme_volet_nom,
                            cp.bloc AS caisson_pvc_nom,
                            t.`type` AS tablier_nom,
                            tee.nom AS teinte_encadrement_elargi_nom,
                            tes.nom AS teinte_encadrement_specifique_nom,
                            ns.nom AS nuancier_standard_encadrement_nom,
                            ops.nom AS option_pack_sav_nom
                     FROM conf_volet cv
                     LEFT JOIN gamme_volet gv ON gv.id = cv.gamme_volet_id
                     LEFT JOIN `Caisson_PVC` cp ON cp.id = cv.caisson_pvc_id
                     LEFT JOIN `Tablier` t ON t.id = cv.tablier_id
                     LEFT JOIN teinte_encadrement_elargi tee ON tee.id = cv.teinte_encadrement_elargi_id
                     LEFT JOIN teinte_encadrement_specifique tes ON tes.id = cv.teinte_encadrement_specifique_id
                     LEFT JOIN nuancier_standard ns ON ns.id = cv.nuancier_standard_encadrement_id
                     LEFT JOIN `Option_pack_SAV` ops ON ops.id = cv.option_pack_sav_id
                     WHERE cv.id = :id',
                    ['id' => $projet->getConfVoletId()]
                ) ?: null;

                if ($confVolet !== null) {
                    $confTeinteTablier = $connection->fetchAssociative(
                        'SELECT ctt.*, ns.nom AS nuancier_standard_nom
                         FROM conf_teinte_tablier ctt
                         LEFT JOIN nuancier_standard ns ON ns.id = ctt.nuancier_standard_id
                         WHERE ctt.conf_volet_id = :cv
                         LIMIT 1',
                        ['cv' => (int) $confVolet['id']]
                    ) ?: null;

                    $lignesCommandeVolet = $connection->fetchAllAssociative(
                        'SELECT lc.*, tc.nom AS type_coulisse_nom
                         FROM `Lignes_de_commande_BLOC_N_R_iD4` lc
                         LEFT JOIN type_coulisse tc ON tc.id = lc.type_coulisse_id
                         WHERE lc.conf_volet_id = :cv
                         ORDER BY lc.id',
                        ['cv' => (int) $confVolet['id']]
                    );
                }
            } catch (\Exception) {
                $confVolet = null;
                $confTeinteTablier = null;
                $lignesCommandeVolet = [];
            }
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'confPf' => $confPf,
            'confVolet' => $confVolet,
            'confTeinteTablier' => $confTeinteTablier,
            'lignesCommandeVolet' => $lignesCommandeVolet,
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

    #[Route('/{id}/dupliquer', name: 'app_projet_duplicate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function duplicate(Request $request, Projet $projet): Response
    {
        if (!$this->isCsrfTokenValid('duplicate' . $projet->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_projet_index');
        }

        // 1. Nouveau projet avec les champs de base
        $copie = new Projet();
        $copie->setClient($projet->getClient());
        $copie->setRefClient($projet->getRefClient() . ' - Copie');
        $copie->setLieu($projet->getLieu());
        $copie->setDescription($projet->getDescription());

        $this->entityManager->persist($copie);
        $this->entityManager->flush(); // flush pour avoir l'ID du nouveau projet

        // 2. Copier la configuration ConfPf si elle existe
        if ($projet->getConfPfId()) {
            $confPf = $this->entityManager->getRepository(\App\Entity\ConfPf::class)->find($projet->getConfPfId());
            if ($confPf) {
                $nouvConfPf = new \App\Entity\ConfPf();
                $nouvConfPf->setProjet($copie);
                $nouvConfPf->setProduit($confPf->getProduit());
                $nouvConfPf->setCategorie($confPf->getCategorie());
                $nouvConfPf->setSousCategorie($confPf->getSousCategorie());
                $nouvConfPf->setOuverture($confPf->getOuverture());
                $nouvConfPf->setFournisseur($confPf->getFournisseur());
                $nouvConfPf->setSysteme($confPf->getSysteme());
                $nouvConfPf->setTypeFenetrePorte($confPf->getTypeFenetrePorte());
                $nouvConfPf->setVitrage($confPf->getVitrage());
                $nouvConfPf->setSensOuverture($confPf->getSensOuverture());
                $nouvConfPf->setCouleurInterieur($confPf->getCouleurInterieur());
                $nouvConfPf->setCouleurExterieur($confPf->getCouleurExterieur());
                $nouvConfPf->setLargeur($confPf->getLargeur());
                $nouvConfPf->setHauteur($confPf->getHauteur());
                $nouvConfPf->setQuantite($confPf->getQuantite());
                $nouvConfPf->setNotes($confPf->getNotes());
                $nouvConfPf->setPosition($confPf->getPosition());
                $nouvConfPf->setPoseType($confPf->getPoseType());

                $this->entityManager->persist($nouvConfPf);
                $this->entityManager->flush();

                $copie->setConfPfId($nouvConfPf->getId());
                $this->entityManager->flush();
            }
        }

        // 3. Copier la configuration ConfVolet si elle existe
        $confVolet = $projet->getConfVolet();
        if ($confVolet) {
            $nouvConfVolet = new \App\Entity\ConfVolet();
            $nouvConfVolet->setProjet($copie);
            $nouvConfVolet->setGammeVolet($confVolet->getGammeVolet());
            $nouvConfVolet->setCaissonPvc($confVolet->getCaissonPvc());
            $nouvConfVolet->setTablier($confVolet->getTablier());
            $nouvConfVolet->setTeinteEncadrementElargi($confVolet->getTeinteEncadrementElargi());
            $nouvConfVolet->setTeinteEncadrementSpecifique($confVolet->getTeinteEncadrementSpecifique());
            $nouvConfVolet->setNuancierStandardEncadrement($confVolet->getNuancierStandardEncadrement());
            $nouvConfVolet->setOptionPackSav($confVolet->getOptionPackSav());
            $nouvConfVolet->setNom($confVolet->getNom());
            $nouvConfVolet->setOptionAutreTeinte($confVolet->getOptionAutreTeinte());
            $nouvConfVolet->setCmgGroupeClimatPlus($confVolet->getCmgGroupeClimatPlus());
            $nouvConfVolet->setExtensionOffre($confVolet->getExtensionOffre());
            $nouvConfVolet->setFaceExterieureAlu($confVolet->getFaceExterieureAlu());
            $nouvConfVolet->setPhtN($confVolet->getPhtN());
            $nouvConfVolet->setPhtR($confVolet->getPhtR());
            $nouvConfVolet->setH4cHorloge4Canaux($confVolet->getH4cHorloge4Canaux());
            $nouvConfVolet->setDiaIdiamant($confVolet->getDiaIdiamant());
            $nouvConfVolet->setSmuSupportMural3Boutons($confVolet->getSmuSupportMural3Boutons());
            $nouvConfVolet->setInvAvecInverseur($confVolet->getInvAvecInverseur());

            $this->entityManager->persist($nouvConfVolet);
            $this->entityManager->flush(); // flush pour avoir l'ID du nouveau confVolet

            // 3a. Copier la teinte tablier (conf_teinte_tablier)
            $confTeinteTablier = $this->entityManager->getRepository(\App\Entity\ConfTeinteTablier::class)
                ->findOneBy(['confVolet' => $confVolet]);
            if ($confTeinteTablier) {
                $nouvCtt = new \App\Entity\ConfTeinteTablier();
                $nouvCtt->setConfVolet($nouvConfVolet);
                $nouvCtt->setNuancierStandard($confTeinteTablier->getNuancierStandard());
                $nouvCtt->setTablierFaibleEmissivite($confTeinteTablier->isTablierFaibleEmissivite());
                $this->entityManager->persist($nouvCtt);
            }

            // 3b. Copier les lignes de commande (Lignes_de_commande_BLOC_N_R_iD4) via SQL direct
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement(
                'INSERT INTO `Lignes_de_commande_BLOC_N_R_iD4`
                    (conf_volet_id, type_coulisse_id, `Nbre`, `Largeur_(LA)`, `Hauteur_(HC)`,
                     `AT`, `B1`, `B2`, `S1`, `S2`, `Repere`, `Angle`,
                     `Elargisseur_coulisse`, `Câble_longueur_utile_5m`, `Panneau_PV_deporte`)
                 SELECT :new_cv, type_coulisse_id, `Nbre`, `Largeur_(LA)`, `Hauteur_(HC)`,
                     `AT`, `B1`, `B2`, `S1`, `S2`, `Repere`, `Angle`,
                     `Elargisseur_coulisse`, `Câble_longueur_utile_5m`, `Panneau_PV_deporte`
                 FROM `Lignes_de_commande_BLOC_N_R_iD4`
                 WHERE conf_volet_id = :old_cv',
                ['new_cv' => $nouvConfVolet->getId(), 'old_cv' => $confVolet->getId()]
            );

            $copie->setConfVolet($nouvConfVolet);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Le projet "' . $projet->getRefClient() . '" a été dupliqué avec succès.');

        return $this->redirectToRoute('app_projet_show', ['id' => $copie->getId()]);
    }

    #[Route('/{id}/supprimer', name: 'app_projet_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Projet $projet): Response
    {
        if ($this->isCsrfTokenValid('delete' . $projet->getId(), $request->getPayload()->getString('_token'))) {
            try {
                // Supprimer les photos du projet
                $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId();
                if (is_dir($photosDir)) {
                    // Supprimer récursivement le dossier et son contenu
                    $this->deleteDirectory($photosDir);
                }
                
                // Les PDFs et configurations seront supprimés automatiquement grâce à CASCADE
                // ProjetPdf et ConfPf sont liés avec ON DELETE CASCADE
                
                // Maintenant supprimer le projet lui-même
                $this->entityManager->remove($projet);
                $this->entityManager->flush();

                $this->addFlash('success', 'Le projet "' . $projet->getRefClient() . '" et toutes ses données associées ont été supprimés avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression du projet : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_projet_index');
    }

    /**
     * Supprime récursivement un dossier et son contenu
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}