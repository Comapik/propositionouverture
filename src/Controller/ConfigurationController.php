<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Produit;
use App\Entity\ConfPf;
use App\Entity\Fournisseur;
use App\Form\ProductSelectionType;
use App\Form\ConfPfDetailsType;
use App\Service\TypeFenetrePorteCompatibiliteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

/**
 * ConfigurationController following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles configuration form operations
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on abstractions (EntityManagerInterface)
 * 
 * Following DRY principle: Centralized configuration management
 * Following KISS principle: Simple configuration operations
 */
#[Route('/configuration')]
class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeFenetrePorteCompatibiliteService $compatibiliteService
    ) {
    }

    #[Route('/pf/{projet}', name: 'app_configuration_pf', methods: ['GET', 'POST'])]
    public function configurePf(Projet $projet, Request $request): Response
    {
        // Vérifier s'il existe déjà une configuration pour ce projet
        $confPf = $this->entityManager->getRepository(ConfPf::class)
            ->findOneBy(['projet' => $projet]);
        
        if (!$confPf) {
            $confPf = new ConfPf();
            $confPf->setProjet($projet);
        }

        // Si aucune photo n'a été ajoutée et que l'étape n'a pas été ignorée, rediriger vers l'étape photos
        if (!$this->hasPhotos($projet) && !$this->hasPhotosSkipped($projet, $request)) {
            return $this->redirectToRoute('app_configuration_pf_photos', [
                'projet' => $projet->getId()
            ]);
        }

        $form = $this->createForm(ProductSelectionType::class);
        $form->handleRequest($request);

        // Récupérer tous les produits pour l'affichage par images
        $produits = $this->entityManager->getRepository(Produit::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $produit = $data['produit'];

            // Sauvegarder le produit sélectionné
            $confPf->setProduit($produit);
            $this->entityManager->persist($confPf);
            $this->entityManager->flush();

            // Mettre à jour l'ID de configuration dans le projet
            $projet->setConfPfId($confPf->getId());
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit sélectionné : ' . $produit->getNom());

            // Redirect to category selection with the selected product
            return $this->redirectToRoute('app_configuration_pf_category', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        return $this->render('configuration/select_product.html.twig', [
            'projet' => $projet,
            'form' => $form,
            'produits' => $produits,
            'configuration_type' => 'Portes/Fenêtres',
            'confpf' => $confPf,
        ]);
    }

    #[Route('/pf/{projet}/photos', name: 'app_configuration_pf_photos', methods: ['GET', 'POST'])]
    public function configurePfPhotos(Projet $projet, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $uploadedFiles = $request->files->get('photos', []);
            $titles = $request->request->all('titles');
            $skipPhotos = $request->request->get('skip_photos', false);
            
            if ($skipPhotos) {
                // L'utilisateur a choisi d'ignorer les photos
                $this->addFlash('info', 'Étape photos ignorée. Vous pourrez ajouter des photos plus tard.');
                
                // Marquer cette étape comme ignorée pour ce projet dans la session
                $session = $request->getSession();
                $session->set('photos_skipped_' . $projet->getId(), true);
                
                return $this->redirectToRoute('app_configuration_pf', ['projet' => $projet->getId()]);
            }
            
            if (!empty($uploadedFiles)) {
                $uploadCount = $this->handlePhotoUploads($uploadedFiles, $projet, $titles);
                
                if ($uploadCount > 0) {
                    $this->addFlash('success', "$uploadCount photo(s) ajoutée(s) avec succès.");
                    return $this->redirectToRoute('app_configuration_pf', ['projet' => $projet->getId()]);
                } else {
                    $this->addFlash('error', 'Aucune photo valide n\'a pu être téléchargée.');
                }
            } else {
                $this->addFlash('warning', 'Veuillez sélectionner au moins une photo ou ignorer cette étape.');
            }
        }

        // Récupérer les photos déjà existantes pour ce projet
        $existingPhotos = $this->getProjectPhotos($projet);

        return $this->render('configuration/upload_photos.html.twig', [
            'projet' => $projet,
            'existing_photos' => $existingPhotos,
        ]);
    }

    #[Route('/pf/{projet}/photos/delete/{filename}', name: 'app_configuration_pf_delete_photo', methods: ['POST'])]
    public function deletePhoto(Projet $projet, string $filename, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_photo', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_configuration_pf_photos', ['projet' => $projet->getId()]);
        }

        // Sécurité : vérifier que le nom de fichier ne contient pas de caractères dangereux
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            $this->addFlash('error', 'Nom de fichier invalide.');
            return $this->redirectToRoute('app_configuration_pf_photos', ['projet' => $projet->getId()]);
        }

        $photoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId() . '/' . $filename;
        
        if (file_exists($photoPath)) {
            if (unlink($photoPath)) {
                $this->addFlash('success', 'Photo supprimée avec succès.');
            } else {
                $this->addFlash('error', 'Erreur lors de la suppression de la photo.');
            }
        } else {
            $this->addFlash('warning', 'Photo introuvable.');
        }

        return $this->redirectToRoute('app_configuration_pf_photos', ['projet' => $projet->getId()]);
    }

    #[Route('/pf/{projet}/category/{confpf}', name: 'app_configuration_pf_category', methods: ['GET', 'POST'])]
    public function configurePfCategory(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        if (!$confPf->getProduit()) {
            $this->addFlash('error', 'Aucun produit sélectionné. Veuillez d\'abord choisir un produit.');
            return $this->redirectToRoute('app_configuration_pf', ['projet' => $projet->getId()]);
        }

        $produit = $confPf->getProduit();
        $categories = $this->entityManager->getRepository(\App\Entity\Categorie::class)
            ->findByProduit($produit->getId());

        if ($request->isMethod('POST')) {
            $categorieId = $request->request->get('categorie_id');
            if ($categorieId) {
                $categorie = $this->entityManager->getRepository(\App\Entity\Categorie::class)
                    ->find($categorieId);
                
                if ($categorie) {
                    $confPf->setCategorie($categorie);
                    $this->entityManager->flush();
                    
                    $this->addFlash('success', 'Catégorie sélectionnée : ' . $categorie->getNom());
                    
                    // Redirect to subcategory selection
                    return $this->redirectToRoute('app_configuration_pf_subcategory', [
                        'projet' => $projet->getId(),
                        'confpf' => $confPf->getId(),
                    ]);
                }
            }
            
            $this->addFlash('warning', 'Veuillez sélectionner une catégorie.');
        }

        return $this->render('configuration/select_category.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $produit,
            'categories' => $categories,
        ]);
    }

    #[Route('/pf/{projet}/subcategory/{confpf}', name: 'app_configuration_pf_subcategory', methods: ['GET', 'POST'])]
    public function configurePfSubcategory(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        if (!$confPf->getCategorie()) {
            $this->addFlash('error', 'Aucune catégorie sélectionnée. Veuillez d\'abord choisir une catégorie.');
            return $this->redirectToRoute('app_configuration_pf_category', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        $categorie = $confPf->getCategorie();
        $sousCategories = $this->entityManager->getRepository(\App\Entity\SousCategorie::class)
            ->findByCategorie($categorie->getId());

        if ($request->isMethod('POST')) {
            $sousCategorieId = $request->request->get('sous_categorie_id');
            if ($sousCategorieId) {
                $sousCategorie = $this->entityManager->getRepository(\App\Entity\SousCategorie::class)
                    ->find($sousCategorieId);
                
                if ($sousCategorie) {
                    $confPf->setSousCategorie($sousCategorie);
                    $this->entityManager->flush();
                    
                    $this->addFlash('success', 'Sous-catégorie sélectionnée : ' . $sousCategorie->getNom());
                    
                    // Redirect to opening selection
                    return $this->redirectToRoute('app_configuration_pf_ouverture', [
                        'projet' => $projet->getId(),
                        'confpf' => $confPf->getId(),
                    ]);
                }
            }
            
            $this->addFlash('warning', 'Veuillez sélectionner une sous-catégorie.');
        }

        return $this->render('configuration/select_subcategory.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $categorie,
            'sousCategories' => $sousCategories,
        ]);
    }

    #[Route('/pf/{projet}/ouverture/{confpf}', name: 'app_configuration_pf_ouverture', methods: ['GET', 'POST'])]
    public function configurePfOuverture(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        if (!$confPf->getSousCategorie()) {
            $this->addFlash('error', 'Aucune sous-catégorie sélectionnée. Veuillez d\'abord choisir une sous-catégorie.');
            return $this->redirectToRoute('app_configuration_pf_subcategory', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        $sousCategorie = $confPf->getSousCategorie();
        $ouvertures = $this->entityManager->getRepository(\App\Entity\Ouverture::class)
            ->findBySousCategorie($sousCategorie->getId());

        if ($request->isMethod('POST')) {
            $ouvertureId = $request->request->get('ouverture_id');
            if ($ouvertureId) {
                $ouverture = $this->entityManager->getRepository(\App\Entity\Ouverture::class)
                    ->find($ouvertureId);
                
                if ($ouverture) {
                    // Sauvegarder l'ouverture directement dans la relation
                    $confPf->setOuverture($ouverture);
                    $this->entityManager->flush();
                    
                    $this->addFlash('success', 'Ouverture sélectionnée : ' . $ouverture->getNom());
                    
                    // Redirect to detailed configuration
                    return $this->redirectToRoute('app_configuration_pf_details', [
                        'projet' => $projet->getId(),
                        'confpf' => $confPf->getId(),
                    ]);
                }
            }
            
            $this->addFlash('warning', 'Veuillez sélectionner un type d\'ouverture.');
        }

        return $this->render('configuration/select_ouverture.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $confPf->getCategorie(),
            'sousCategorie' => $sousCategorie,
            'ouvertures' => $ouvertures,
        ]);
    }

    #[Route('/pf/{projet}/details/{confpf}', name: 'app_configuration_pf_details', methods: ['GET', 'POST'])]
    public function configurePfDetails(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        // Vérifier que toutes les étapes précédentes sont complètes
        if (!$confPf->getProduit() || !$confPf->getCategorie() || !$confPf->getSousCategorie()) {
            $this->addFlash('error', 'Configuration incomplète. Veuillez recommencer le processus.');
            return $this->redirectToRoute('app_configuration_pf', ['projet' => $projet->getId()]);
        }

        // Récupérer les fournisseurs pour le produit sélectionné
        $fournisseurs = [];
        if ($confPf->getProduit()) {
            $fournisseurs = $this->entityManager->getRepository(\App\Entity\Fournisseur::class)
                ->findByProduit($confPf->getProduit()->getId());
        }

        // Récupérer les types de fenêtre/porte selon l'ouverture ET le système sélectionné
        $typesFenetrePorte = [];
        if ($confPf->getOuverture() && $confPf->getSysteme()) {
            // Utiliser le service de compatibilité
            $typesFenetrePorte = $this->compatibiliteService->getTypesCompatibles(
                $confPf->getOuverture(), 
                $confPf->getSysteme()
            );
        } elseif ($confPf->getOuverture()) {
            // Fallback : si on a seulement l'ouverture
            $typesFenetrePorte = $this->compatibiliteService->getTypesByOuverture($confPf->getOuverture());
        }

        $form = $this->createForm(ConfPfDetailsType::class, $confPf, [
            'fournisseurs' => $fournisseurs,
            'typesFenetrePorte' => $typesFenetrePorte,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les timestamps
            if (method_exists($confPf, 'setUpdatedAt')) {
                $confPf->setUpdatedAt(new \DateTimeImmutable());
            }
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Configuration enregistrée avec succès !');
            
            // Vérifier si un système a été sélectionné, si oui rediriger vers le choix des couleurs
            if ($confPf->getSysteme()) {
                return $this->redirectToRoute('app_configuration_pf_couleurs', [
                    'projet' => $projet->getId(),
                    'confpf' => $confPf->getId(),
                ]);
            }
            
            // Sinon rediriger vers les détails du projet
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('configuration/pf_details.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $confPf->getCategorie(),
            'sousCategorie' => $confPf->getSousCategorie(),
            'form' => $form,
        ]);
    }

    #[Route('/pf/{projet}/couleurs/{confpf}', name: 'app_configuration_pf_couleurs', methods: ['GET', 'POST'])]
    public function configurePfCouleurs(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        // Vérifier que toutes les étapes précédentes sont complètes
        if (!$confPf->getProduit() || !$confPf->getCategorie() || !$confPf->getSousCategorie() || !$confPf->getSysteme()) {
            $this->addFlash('error', 'Configuration incomplète. Veuillez d\'abord compléter les étapes précédentes.');
            return $this->redirectToRoute('app_configuration_pf_details', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        $form = $this->createForm(\App\Form\CouleurSelectionType::class, $confPf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer les couleurs spéciales (blanc et crème)
            $this->handleSpecialColors($confPf);
            
            // Mettre à jour les timestamps
            $confPf->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
            
            $couleurInterieur = $confPf->getCouleurInterieur();
            $couleurExterieur = $confPf->getCouleurExterieur();
            
            $message = 'Couleurs sélectionnées : ';
            if ($couleurInterieur) {
                $message .= 'Intérieur: ' . $couleurInterieur->getNom();
            }
            if ($couleurExterieur) {
                if ($couleurInterieur) {
                    $message .= ', ';
                }
                $message .= 'Extérieur: ' . $couleurExterieur->getNom();
            }
            
            $this->addFlash('success', $message);
            
            // Rediriger vers les détails du projet (ou prochaine étape si il y en a une)
            return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
        }

        return $this->render('configuration/select_couleurs.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $confPf->getCategorie(),
            'sousCategorie' => $confPf->getSousCategorie(),
            'systeme' => $confPf->getSysteme(),
            'form' => $form,
        ]);
    }

    #[Route('/api/systemes/{fournisseur}', name: 'app_api_systemes_by_fournisseur', methods: ['GET'])]
    public function getSystemesByFournisseur(Fournisseur $fournisseur): JsonResponse
    {
        $systemes = $this->entityManager->getRepository(\App\Entity\Systeme::class)
            ->findByFournisseur($fournisseur);
        
        $data = [];
        foreach ($systemes as $systeme) {
            $ouvertures = [];
            foreach ($systeme->getOuvertures() as $ouverture) {
                $ouvertures[] = [
                    'id' => $ouverture->getId(),
                    'nom' => $ouverture->getNom()
                ];
            }
            
            $data[] = [
                'id' => $systeme->getId(),
                'nom' => $systeme->getNom(),
                'urlImage' => $systeme->getUrlImage(),
                'ouvertures' => $ouvertures,
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/api/systemes/{fournisseur}/{ouverture}', name: 'app_api_systemes_by_fournisseur_and_ouverture', methods: ['GET'])]
    public function getSystemesByFournisseurAndOuverture(Fournisseur $fournisseur, \App\Entity\Ouverture $ouverture): JsonResponse
    {
        $systemes = $this->entityManager->getRepository(\App\Entity\Systeme::class)
            ->findByFournisseurAndOuverture($fournisseur->getId(), $ouverture->getId());
        
        $data = [];
        foreach ($systemes as $systeme) {
            $data[] = [
                'id' => $systeme->getId(),
                'nom' => $systeme->getNom(),
                'urlImage' => $systeme->getUrlImage(),
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/api/types-fenetre-porte/{systeme}', name: 'app_api_types_fenetre_porte_by_systeme', methods: ['GET'])]
    public function getTypesFenetrePorteBySysteme(\App\Entity\Systeme $systeme, Request $request): JsonResponse
    {
        $ouvertureId = $request->query->get('ouverture');
        
        if ($ouvertureId) {
            // Utiliser la nouvelle table de compatibilité pour obtenir les types compatibles
            $ouverture = $this->entityManager->getRepository(\App\Entity\Ouverture::class)
                ->find((int)$ouvertureId);
                
            if (!$ouverture) {
                return new JsonResponse(['error' => 'Ouverture introuvable'], 404);
            }
            
            $typesFenetrePorte = $this->entityManager
                ->getRepository(\App\Entity\TypeFenetrePorteCompatibilite::class)
                ->findTypesFenetrePorteByOuvertureAndSysteme($ouverture, $systeme);
        } else {
            // Utiliser l'ancienne méthode temporairement si pas d'ouverture
            $typesFenetrePorte = $this->entityManager->getRepository(\App\Entity\TypeFenetrePorte::class)
                ->findBySysteme($systeme->getId());
        }
        
        $data = [];
        foreach ($typesFenetrePorte as $type) {
            $data[] = [
                'id' => $type->getId(),
                'nom' => $type->getNom(),
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/volet/{projet}', name: 'app_configuration_volet', methods: ['GET', 'POST'])]
    public function configureVolet(Projet $projet, Request $request): Response
    {
        // TODO: Implement shutter configuration
        return $this->render('configuration/volet.html.twig', [
            'projet' => $projet,
        ]);
    }

    /**
     * Gérer les couleurs spéciales (blanc et crème) en créant les entités si nécessaire
     */
    private function handleSpecialColors(ConfPf $confPf): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (!$request) {
            return;
        }
        
        $couleurRepo = $this->entityManager->getRepository(\App\Entity\Couleur::class);
        
        // Récupérer les valeurs raw du formulaire pour détecter les couleurs spéciales
        $formData = $request->request->all('couleur_selection');
        
        // Vérifier et traiter la couleur intérieure
        if (isset($formData['couleurInterieur'])) {
            $couleurInterieure = $this->processSpecialColor($formData['couleurInterieur'], $couleurRepo);
            if ($couleurInterieure) {
                $confPf->setCouleurInterieur($couleurInterieure);
            }
        }
        
        // Vérifier et traiter la couleur extérieure
        if (isset($formData['couleurExterieur'])) {
            $couleurExterieure = $this->processSpecialColor($formData['couleurExterieur'], $couleurRepo);
            if ($couleurExterieure) {
                $confPf->setCouleurExterieur($couleurExterieure);
            }
        }
    }

    /**
     * Traiter une couleur spéciale individuelle
     */
    private function processSpecialColor(string $couleurId, $couleurRepo): ?\App\Entity\Couleur
    {
        // Si ce n'est pas une couleur spéciale, retourner la couleur existante
        if (!str_starts_with($couleurId, 'special_')) {
            return $couleurRepo->find($couleurId);
        }
        
        // Traiter les couleurs spéciales
        if ($couleurId === 'special_blanc') {
            return $this->getOrCreateSpecialColor('Blanc', '#FFFFFF', $couleurRepo);
        } elseif ($couleurId === 'special_creme') {
            return $this->getOrCreateSpecialColor('Crème', '#F5F5DC', $couleurRepo);
        }
        
        return null;
    }

    /**
     * Récupérer ou créer une couleur spéciale
     */
    private function getOrCreateSpecialColor(string $nom, string $codeHex, $couleurRepo): \App\Entity\Couleur
    {
        // Chercher si la couleur existe déjà
        $couleur = $couleurRepo->findOneBy(['nom' => $nom, 'codeHex' => $codeHex]);
        
        if (!$couleur) {
            // Créer la nouvelle couleur
            $couleur = new \App\Entity\Couleur();
            $couleur->setNom($nom);
            $couleur->setCodeHex($codeHex);
            $couleur->setPlaxageLaquageId(99); // ID spécial pour les couleurs fixes
            
            $this->entityManager->persist($couleur);
            $this->entityManager->flush();
        }
        
        return $couleur;
    }

    /**
     * Vérifier si le projet a des photos
     */
    private function hasPhotos(Projet $projet): bool
    {
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId();
        return is_dir($photosDir) && count(glob($photosDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE)) > 0;
    }

    /**
     * Vérifier si l'utilisateur a choisi d'ignorer l'étape photos pour ce projet
     */
    private function hasPhotosSkipped(Projet $projet, Request $request): bool
    {
        $session = $request->getSession();
        return $session->get('photos_skipped_' . $projet->getId(), false);
    }

    /**
     * Gérer le téléchargement de photos
     */
    private function handlePhotoUploads(array $uploadedFiles, Projet $projet, array $titles = []): int
    {
        $uploadCount = 0;
        $errorDetails = [];
        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId();
        $metadataFile = $uploadsDirectory . '/metadata.json';
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadsDirectory)) {
            if (!mkdir($uploadsDirectory, 0755, true)) {
                throw new \Exception("Impossible de créer le répertoire d'upload : $uploadsDirectory");
            }
        }

        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($uploadsDirectory)) {
            throw new \Exception("Le répertoire d'upload n'est pas accessible en écriture : $uploadsDirectory");
        }

        // Charger les métadonnées existantes
        $metadata = [];
        if (file_exists($metadataFile)) {
            $existingData = file_get_contents($metadataFile);
            $metadata = json_decode($existingData, true) ?: [];
        }

        foreach ($uploadedFiles as $index => $uploadedFile) {
            if (!$uploadedFile) {
                $errorDetails[] = "Fichier $index: null";
                continue;
            }
            
            if (!$uploadedFile->isValid()) {
                $errorDetails[] = "Fichier $index: invalide - Erreur: " . $uploadedFile->getErrorMessage();
                continue;
            }

            // Capturer les informations du fichier AVANT toute manipulation
            $originalName = $uploadedFile->getClientOriginalName();
            $fileMimeType = $uploadedFile->getMimeType();
            $fileSize = $uploadedFile->getSize();
            $fileExtension = $uploadedFile->guessExtension();

            // Valider le type de fichier
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileMimeType, $allowedMimes)) {
                $errorDetails[] = "Fichier $index ($originalName): type MIME non autorisé ($fileMimeType)";
                continue;
            }

            // Valider la taille selon les limites PHP
            $maxUploadSize = min(
                $this->parseSize(ini_get('upload_max_filesize')),
                $this->parseSize(ini_get('post_max_size'))
            );
            if ($fileSize > $maxUploadSize) {
                $errorDetails[] = "Fichier $index ($originalName): trop volumineux (" . round($fileSize / 1024 / 1024, 2) . " MB, max: " . round($maxUploadSize / 1024 / 1024, 2) . " MB)";
                continue;
            }

            // Générer un nom de fichier unique
            $originalFilename = pathinfo($originalName, PATHINFO_FILENAME);
            // Nettoyer le nom de fichier (alternative à transliterator_transliterate)
            $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalFilename);
            $safeFilename = strtolower($safeFilename);
            $fileName = $safeFilename . '_' . uniqid() . '.' . $fileExtension;

            try {
                // Déplacer le fichier
                $uploadedFile->move($uploadsDirectory, $fileName);
                
                // Ajouter les métadonnées de la photo
                $photoTitle = isset($titles[$index]) && !empty(trim($titles[$index])) 
                    ? trim($titles[$index]) 
                    : $originalFilename;
                    
                $metadata[$fileName] = [
                    'title' => $photoTitle,
                    'originalName' => $originalName,
                    'uploadDate' => date('Y-m-d H:i:s'),
                    'size' => $fileSize,
                    'mimeType' => $fileMimeType
                ];
                
                $uploadCount++;
            } catch (\Exception $e) {
                $errorDetails[] = "Fichier $index ($originalName): erreur lors du déplacement - " . $e->getMessage();
                continue;
            }
        }

        // Sauvegarder les métadonnées mises à jour
        if ($uploadCount > 0) {
            file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // Ajouter les détails des erreurs aux messages flash pour le débogage
        if (!empty($errorDetails)) {
            $this->addFlash('warning', 'Détails des erreurs: ' . implode(' | ', $errorDetails));
        }

        return $uploadCount;
    }

    /**
     * Convertir les valeurs de taille PHP (comme "2M", "8G") en bytes
     */
    private function parseSize(string $size): int
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int)substr($size, 0, -1);
        
        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Récupérer les photos d'un projet
     */
    private function getProjectPhotos(Projet $projet): array
    {
        $photosDir = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projet->getId();
        $metadataFile = $photosDir . '/metadata.json';
        $photos = [];
        $metadata = [];

        // Charger les métadonnées si elles existent
        if (file_exists($metadataFile)) {
            $metadataContent = file_get_contents($metadataFile);
            $metadata = json_decode($metadataContent, true) ?: [];
        }

        if (is_dir($photosDir)) {
            $files = glob($photosDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            foreach ($files as $file) {
                $filename = basename($file);
                $fileMetadata = $metadata[$filename] ?? [];
                
                $photos[] = [
                    'filename' => $filename,
                    'path' => $this->generateUrl('app_serve_photo', [
                        'projetId' => $projet->getId(),
                        'filename' => $filename
                    ]),
                    'title' => $fileMetadata['title'] ?? pathinfo($filename, PATHINFO_FILENAME),
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                    'originalName' => $fileMetadata['originalName'] ?? $filename,
                    'uploadDate' => $fileMetadata['uploadDate'] ?? date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
            
            // Trier par date de modification (plus récent en premier)
            usort($photos, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }

        return $photos;
    }

    #[Route('/uploads/projets/{projetId}/{filename}', name: 'app_serve_photo', methods: ['GET'])]
    public function servePhoto(int $projetId, string $filename): Response
    {
        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/projets/' . $projetId;
        $filePath = $uploadsDirectory . '/' . $filename;

        // Sécurité : vérifier que le fichier existe et est dans le bon répertoire
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw $this->createNotFoundException('Image non trouvée');
        }

        // Sécurité : vérifier que le chemin ne contient pas de traversée de répertoire
        $realPath = realpath($filePath);
        $realUploadDir = realpath($uploadsDirectory);
        if (!$realPath || !str_starts_with($realPath, $realUploadDir)) {
            throw $this->createNotFoundException('Accès non autorisé');
        }

        // Déterminer le type MIME
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Créer la réponse avec les en-têtes appropriés
        $response = new Response();
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', (string) filesize($filePath));
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        $response->setContent(file_get_contents($filePath));

        return $response;
    }
}