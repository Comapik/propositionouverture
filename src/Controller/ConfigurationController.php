<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Produit;
use App\Entity\ConfPf;
use App\Entity\Fournisseur;
use App\Entity\GammeVolet;
use App\Entity\TypeFenetrePorte;
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
    /**
     * Mapping nom de gamme normalise -> template de configuration commande volet.
     */
    private const VOLET_COMMANDE_TEMPLATE_MAP = [
        'bloc-n-r-id4' => 'configuration/volet/commande/bloc_n_r_id4.html.twig',
    ];

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

        // Rediriger vers l'étape photos si elle n'a pas encore été visitée (photos ajoutées ou étape ignorée)
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
                    
                    // Vérifier si on est dans le contexte des portes avec orientation
                    if ($this->isDoorWithOrientationContext($confPf)) {
                        return $this->redirectToRoute('app_configuration_pf_orientation', [
                            'projet' => $projet->getId(),
                            'confpf' => $confPf->getId(),
                        ]);
                    }
                    
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

        // Filtrer les ouvertures selon le sens choisi si on est dans le contexte porte avec orientation
        if ($this->isDoorWithOrientationContext($confPf) && $confPf->getSensOuverture()) {
            $ouvertures = array_filter($ouvertures, function($ouverture) use ($confPf) {
                $sensOuverture = $ouverture->getSensOuverture();
                // Inclure les ouvertures qui correspondent au sens choisi ou qui n'ont pas de sens défini
                return !$sensOuverture || $sensOuverture === $confPf->getSensOuverture();
            });
        }

        if ($request->isMethod('POST')) {
            $ouvertureId = $request->request->get('ouverture_id');
            if ($ouvertureId) {
                $ouverture = $this->entityManager->getRepository(\App\Entity\Ouverture::class)
                    ->find($ouvertureId);
                
                if ($ouverture) {
                    // Sauvegarder l'ouverture directement dans la relation
                    $confPf->setOuverture($ouverture);
                    $confPf->setTypeFenetrePorte(null); // Réinitialiser le type
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

    #[Route('/pf/{projet}/orientation/{confpf}', name: 'app_configuration_pf_orientation', methods: ['GET', 'POST'])]
    public function configurePfOrientation(
        #[MapEntity(mapping: ['projet' => 'id'])] Projet $projet,
        #[MapEntity(mapping: ['confpf' => 'id'])] ConfPf $confPf,
        Request $request
    ): Response {
        if (!$confPf->getSousCategorie()) {
            $this->addFlash('error', 'Veuillez sélectionner une sous-catégorie avant de choisir le sens.');
            return $this->redirectToRoute('app_configuration_pf_subcategory', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        // Vérifier si on est dans le contexte des portes avec orientation
        if (!$this->isDoorWithOrientationContext($confPf)) {
            // Si ce n'est pas des portes alu, passer directement à l'étape suivante
            return $this->redirectToRoute('app_configuration_pf_ouverture', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId(),
            ]);
        }

        $orientationOptions = ['int', 'ext'];
        $labels = [
            'ext' => "Portes ouverture vers l'extérieur",
            'int' => "Portes ouverture vers l'intérieur",
        ];

        if ($request->isMethod('POST')) {
            $sens = $request->request->get('sens_ouverture');
            if ($sens && in_array($sens, $orientationOptions, true)) {
                $confPf->setSensOuverture($sens);
                $this->entityManager->flush();

                $this->addFlash('success', 'Sens choisi : ' . ($labels[$sens] ?? $sens));
                return $this->redirectToRoute('app_configuration_pf_ouverture', [
                    'projet' => $projet->getId(),
                    'confpf' => $confPf->getId(),
                ]);
            }

            $this->addFlash('warning', 'Veuillez choisir un sens d\'ouverture.');
        }

        return $this->render('configuration/select_orientation.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $confPf->getCategorie(),
            'sousCategorie' => $confPf->getSousCategorie(),
            'orientationOptions' => $orientationOptions,
            'labels' => $labels,
        ]);
    }

    /**
     * Retourne les sens d'ouverture disponibles pour la configuration en cours.
     *
     * @return string[]
     */
    private function getOrientationOptions(ConfPf $confPf): array
    {
        // Si le contexte n'est pas une porte, ne pas proposer d'orientation
        if (!$this->isDoorWithOrientationContext($confPf)) {
            return [];
        }

        $ouverture = $confPf->getOuverture();
        if (!$ouverture) {
            return [];
        }

        // Vérifier si l'ouverture a un sens défini
        $sens = $ouverture->getSensOuverture();
        if ($sens && in_array($sens, ['int', 'ext'], true)) {
            return [$sens];
        }

        // Fallback heuristique pour les portes avec orientation (alu, PVC d'entrée, PVC de service)
        if ($this->isDoorWithOrientationContext($confPf)) {
            return ['int', 'ext'];
        }

        return [];
    }

    private function shouldAskOrientation(array $orientationOptions): bool
    {
        $filtered = array_values(array_intersect($orientationOptions, ['int', 'ext']));
        return count($filtered) > 1;
    }

    private function isDoorWithOrientationContext(ConfPf $confPf): bool
    {
        $productName = strtolower((string) $confPf->getProduit()?->getNom());
        $categoryName = strtolower((string) $confPf->getCategorie()?->getNom());
        $subCategoryName = strtolower((string) $confPf->getSousCategorie()?->getNom());

        // Exclure tout ce qui est coulissant (dans n'importe quel libellé)
        if (str_contains($productName . ' ' . $categoryName . ' ' . $subCategoryName, 'couliss')) {
            return false;
        }

        // Exclure explicitement la sous-catégorie Fenêtre PVC (avec ou sans accent)
        if (
            str_contains($subCategoryName, 'fenêtre pvc') ||
            str_contains($subCategoryName, 'fenetre pvc') ||
            (str_contains($subCategoryName, 'fenetre') && str_contains($subCategoryName, 'pvc')) ||
            (str_contains($subCategoryName, 'fenêtre') && str_contains($subCategoryName, 'pvc'))
        ) {
            return false;
        }

        // Étape limitée aux PRODUITS contenant explicitement "porte"
        if (!str_contains($productName, 'porte')) {
            return false;
        }

        return true;
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

        $orientationOptions = [];
        if ($confPf->getOuverture()) {
            $orientationOptions = $this->getOrientationOptions($confPf);
            if ($this->shouldAskOrientation($orientationOptions) && !$confPf->getSensOuverture()) {
                $this->addFlash('warning', 'Veuillez sélectionner le sens d\'ouverture.');
                return $this->redirectToRoute('app_configuration_pf_orientation', [
                    'projet' => $projet->getId(),
                    'confpf' => $confPf->getId(),
                ]);
            }
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
            
            // Rediriger vers la génération PDF (dernière étape)
            return $this->redirectToRoute('app_configuration_pf_pdf', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId()
            ]);
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
            // Les couleurs sont automatiquement transformées par le CouleurTransformer
            
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
            
            // Rediriger vers la génération PDF (dernière étape)
            return $this->redirectToRoute('app_configuration_pf_pdf', [
                'projet' => $projet->getId(),
                'confpf' => $confPf->getId()
            ]);
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
        if ($request->isMethod('POST')) {
            $gammeId4 = $request->request->get('gamme_id4');
            
            if ($gammeId4) {
                // Stocker la gamme en session pour la suite du processus
                $session = $request->getSession();
                $session->set('volet_config', [
                    'projet_id' => $projet->getId(),
                    'gamme_id4' => $gammeId4,
                ]);

                // Rediriger vers l'étape suivante : Commande Volet
                return $this->redirectToRoute('app_configuration_volet_commande', [
                    'projet' => $projet->getId(),
                ]);
            }

            $this->addFlash('warning', 'Veuillez sélectionner une gamme iD4');
        }

        // Récupérer toutes les gammes de volets depuis la base de données
        $gammes = $this->entityManager->getRepository(\App\Entity\GammeVolet::class)->findAll();

        return $this->render('configuration/volet.html.twig', [
            'projet' => $projet,
            'gammes' => $gammes,
        ]);
    }

    #[Route('/volet/{projet}/commande', name: 'app_configuration_volet_commande', methods: ['GET', 'POST'])]
    public function configureVoletCommande(Projet $projet, Request $request): Response
    {
        // Vérifier que la gamme a été sélectionnée
        $session = $request->getSession();
        $voletConfig = $session->get('volet_config');

        if (!$voletConfig || $voletConfig['projet_id'] !== $projet->getId()) {
            $this->addFlash('warning', 'Veuillez d\'abord sélectionner une gamme iD4');
            return $this->redirectToRoute('app_configuration_volet', [
                'projet' => $projet->getId(),
            ]);
        }

        $gammeId4 = (string) $voletConfig['gamme_id4'];
        $gammeSelectionneeNom = $gammeId4;
        $gammeEntity = null;
        if (preg_match('/^gamme-(\d+)$/', $gammeId4, $matches) === 1) {
            $gammeEntity = $this->entityManager->getRepository(\App\Entity\GammeVolet::class)->find((int) $matches[1]);
            if ($gammeEntity !== null && $gammeEntity->getNom() !== null) {
                $gammeSelectionneeNom = $gammeEntity->getNom();
            }
        }

        // Récupérer toutes les données de référence pour le formulaire
        $connection = $this->entityManager->getConnection();

        // --- Gestion POST : sauvegarde teinte tablier dans conf_teinte_tablier ---
        if ($request->isMethod('POST')) {
            $nuancierStandardId = $request->request->get('nuancier_standard_id') ?: null;
            $tablierFaibleEmissivite = $request->request->get('tablier_faible_emissivite') ? true : false;
            $extensionOffreActive = $request->request->get('exo') ? true : false;
            $caissonPvcId = $request->request->get('caisson_pvc_id') ?: null;
            $tablierId = $request->request->get('tablier_id') ?: null;
            $teinteEncadrementElargiId = $request->request->get('teinte_encadrement_elargi_id') ?: null;
            $teinteEncadrementSpecifiqueId = $request->request->get('teinte_encadrement_specifique_id') ?: null;
            $nuancierStandardEncadrementId = $request->request->get('nuancier_standard_encadrement_id') ?: null;
            $optionPackSavId = $request->request->get('option_pack_sav_id') ?: null;
            $faceExterieureAlu = $request->request->get('face_exterieure_alu') ? true : false;
            $optionAutreTeinte = $request->request->get('option_autre_teinte') ?: null;
            $phtN = $request->request->get('pht_n') ? true : false;
            $phtR = $request->request->get('pht_r') ? true : false;
            $cmgGroupeClimatPlus = $request->request->get('cmg_groupe_climat_plus') !== null && $request->request->get('cmg_groupe_climat_plus') !== ''
                ? (int) $request->request->get('cmg_groupe_climat_plus')
                : null;
            $h4cHorloge4Canaux = $request->request->get('h4c_horloge_4_canaux') ? true : false;
            $diaIDiamant = $request->request->get('dia_idiamant') ? true : false;
            $smuSupportMural3Boutons = $request->request->get('smu_support_mural_3_boutons') ? true : false;
            $invAvecInverseur = $request->request->get('inv_avec_inverseur') ? true : false;
            $lignesCommandePayload = $request->request->all('lignes');
            if (!is_array($lignesCommandePayload)) {
                $lignesCommandePayload = [];
            }

            // Récupérer ou créer le ConfVolet lié au projet
            $confVolet = $projet->getConfVolet();
            if ($confVolet === null) {
                $confVolet = new \App\Entity\ConfVolet();
                $confVolet->setProjet($projet);
                if ($gammeEntity !== null) {
                    $confVolet->setGammeVolet($gammeEntity);
                }
                $this->entityManager->persist($confVolet);
                $this->entityManager->flush();
                $projet->setConfVolet($confVolet);
                $this->entityManager->flush();
            }

            $confVoletId = $confVolet->getId();

            $connection->executeStatement(
                'UPDATE conf_volet SET
                    Extension_offre = :exo,
                    caisson_pvc_id = :caisson,
                    tablier_id = :tablier,
                    teinte_encadrement_elargi_id = :tee,
                    teinte_encadrement_specifique_id = :tes,
                    nuancier_standard_encadrement_id = :nse,
                    option_pack_sav_id = :ops,
                    face_exterieure_alu = :fea,
                    option_autre_teinte = :oat,
                    pht_n = :phtn,
                    pht_r = :phtr,
                    cmg_groupe_climat_plus = :cmg,
                    h4c_horloge_4_canaux = :h4c,
                    dia_idiamant = :dia,
                    smu_support_mural_3_boutons = :smu,
                    inv_avec_inverseur = :inv
                 WHERE id = :id',
                [
                    'exo' => $extensionOffreActive ? chr(1) : chr(0),
                    'caisson' => $caissonPvcId !== null ? (int) $caissonPvcId : null,
                    'tablier' => $tablierId !== null ? (int) $tablierId : null,
                    'tee' => $teinteEncadrementElargiId !== null ? (int) $teinteEncadrementElargiId : null,
                    'tes' => $teinteEncadrementSpecifiqueId !== null ? (int) $teinteEncadrementSpecifiqueId : null,
                    'nse' => $nuancierStandardEncadrementId !== null ? (int) $nuancierStandardEncadrementId : null,
                    'ops' => $optionPackSavId !== null ? (int) $optionPackSavId : null,
                    'fea' => $faceExterieureAlu ? chr(1) : chr(0),
                    'oat' => $optionAutreTeinte,
                    'phtn' => $phtN ? chr(1) : chr(0),
                    'phtr' => $phtR ? chr(1) : chr(0),
                    'cmg' => $cmgGroupeClimatPlus,
                    'h4c' => $h4cHorloge4Canaux ? chr(1) : chr(0),
                    'dia' => $diaIDiamant ? chr(1) : chr(0),
                    'smu' => $smuSupportMural3Boutons ? chr(1) : chr(0),
                    'inv' => $invAvecInverseur ? chr(1) : chr(0),
                    'id' => $confVoletId,
                ]
            );

            // Upsert conf_teinte_tablier
            $existing = $connection->fetchOne(
                'SELECT id FROM conf_teinte_tablier WHERE conf_volet_id = :cv',
                ['cv' => $confVoletId]
            );

            if ($existing) {
                $connection->executeStatement(
                    'UPDATE conf_teinte_tablier SET nuancier_standard_id = :ns, Tablier_faible_emissivite = :tfe WHERE conf_volet_id = :cv',
                    [
                        'ns'  => $nuancierStandardId !== null ? (int) $nuancierStandardId : null,
                        'tfe' => $tablierFaibleEmissivite ? chr(1) : chr(0),
                        'cv'  => $confVoletId,
                    ]
                );
            } else {
                $connection->executeStatement(
                    'INSERT INTO conf_teinte_tablier (conf_volet_id, nuancier_standard_id, Tablier_faible_emissivite) VALUES (:cv, :ns, :tfe)',
                    [
                        'cv'  => $confVoletId,
                        'ns'  => $nuancierStandardId !== null ? (int) $nuancierStandardId : null,
                        'tfe' => $tablierFaibleEmissivite ? chr(1) : chr(0),
                    ]
                );
            }

            foreach ($lignesCommandePayload as $lignePayload) {
                if (!is_array($lignePayload)) {
                    continue;
                }

                $ligneCommandeId = isset($lignePayload['id']) && $lignePayload['id'] !== '' ? (int) $lignePayload['id'] : null;
                $ligneCommandeData = [
                    'Repere' => isset($lignePayload['Repere']) && $lignePayload['Repere'] !== '' ? (string) $lignePayload['Repere'] : null,
                    'Nbre' => isset($lignePayload['Nbre']) && $lignePayload['Nbre'] !== '' ? (int) $lignePayload['Nbre'] : null,
                    'Largeur_(LA)' => isset($lignePayload['Largeur_(LA)']) && $lignePayload['Largeur_(LA)'] !== '' ? (int) $lignePayload['Largeur_(LA)'] : null,
                    'Hauteur_(HC)' => isset($lignePayload['Hauteur_(HC)']) && $lignePayload['Hauteur_(HC)'] !== '' ? (int) $lignePayload['Hauteur_(HC)'] : null,
                    'AT' => isset($lignePayload['AT']) && $lignePayload['AT'] !== '' ? (int) $lignePayload['AT'] : null,
                    'B1' => isset($lignePayload['B1']) && $lignePayload['B1'] !== '' ? (int) $lignePayload['B1'] : null,
                    'B2' => isset($lignePayload['B2']) && $lignePayload['B2'] !== '' ? (int) $lignePayload['B2'] : null,
                    'S1' => isset($lignePayload['S1']) && $lignePayload['S1'] !== '' ? (int) $lignePayload['S1'] : null,
                    'S2' => isset($lignePayload['S2']) && $lignePayload['S2'] !== '' ? (int) $lignePayload['S2'] : null,
                    'Angle' => isset($lignePayload['Angle']) && $lignePayload['Angle'] !== '' ? (int) $lignePayload['Angle'] : null,
                    'Elargisseur_coulisse' => (($lignePayload['Elargisseur_coulisse'] ?? '0') === '1') ? chr(1) : chr(0),
                    'Câble_longueur_utile_5m' => (($lignePayload['Câble_longueur_utile_5m'] ?? '0') === '1') ? chr(1) : chr(0),
                    'Panneau_PV_deporte' => (($lignePayload['Panneau_PV_deporte'] ?? '0') === '1') ? chr(1) : chr(0),
                    'type_coulisse_id' => isset($lignePayload['type_coulisse_id']) && $lignePayload['type_coulisse_id'] !== '' ? (int) $lignePayload['type_coulisse_id'] : null,
                ];

                $hasLigneCommandeData = false;
                foreach ($ligneCommandeData as $column => $value) {
                    if (in_array($column, ['Elargisseur_coulisse', 'Câble_longueur_utile_5m', 'Panneau_PV_deporte'], true)) {
                        if ($value === chr(1)) {
                            $hasLigneCommandeData = true;
                            break;
                        }

                        continue;
                    }

                    if ($value !== null && $value !== '') {
                        $hasLigneCommandeData = true;
                        break;
                    }
                }

                if (!$hasLigneCommandeData) {
                    continue;
                }

                if ($ligneCommandeId !== null) {
                    $connection->executeStatement(
                        'UPDATE `Lignes_de_commande_BLOC_N_R_iD4` SET
                            `Repere` = :repere,
                            `Nbre` = :nbre,
                            `Largeur_(LA)` = :largeur,
                            `Hauteur_(HC)` = :hauteur,
                            `AT` = :at,
                            `B1` = :b1,
                            `B2` = :b2,
                            `S1` = :s1,
                            `S2` = :s2,
                            `Angle` = :angle,
                            `Elargisseur_coulisse` = :elargisseur,
                            `Câble_longueur_utile_5m` = :cable,
                            `Panneau_PV_deporte` = :panneau,
                            `type_coulisse_id` = :type_coulisse,
                            `conf_volet_id` = :conf_volet
                         WHERE id = :id AND conf_volet_id = :conf_volet',
                        [
                            'repere' => $ligneCommandeData['Repere'],
                            'nbre' => $ligneCommandeData['Nbre'],
                            'largeur' => $ligneCommandeData['Largeur_(LA)'],
                            'hauteur' => $ligneCommandeData['Hauteur_(HC)'],
                            'at' => $ligneCommandeData['AT'],
                            'b1' => $ligneCommandeData['B1'],
                            'b2' => $ligneCommandeData['B2'],
                            's1' => $ligneCommandeData['S1'],
                            's2' => $ligneCommandeData['S2'],
                            'angle' => $ligneCommandeData['Angle'],
                            'elargisseur' => $ligneCommandeData['Elargisseur_coulisse'],
                            'cable' => $ligneCommandeData['Câble_longueur_utile_5m'],
                            'panneau' => $ligneCommandeData['Panneau_PV_deporte'],
                            'type_coulisse' => $ligneCommandeData['type_coulisse_id'],
                            'conf_volet' => $confVoletId,
                            'id' => $ligneCommandeId,
                        ]
                    );
                } else {
                    $connection->executeStatement(
                        'INSERT INTO `Lignes_de_commande_BLOC_N_R_iD4`
                            (`Repere`, `Nbre`, `Largeur_(LA)`, `Hauteur_(HC)`, `AT`, `B1`, `B2`, `S1`, `S2`, `Angle`, `Elargisseur_coulisse`, `Câble_longueur_utile_5m`, `Panneau_PV_deporte`, `type_coulisse_id`, `conf_volet_id`)
                         VALUES
                            (:repere, :nbre, :largeur, :hauteur, :at, :b1, :b2, :s1, :s2, :angle, :elargisseur, :cable, :panneau, :type_coulisse, :conf_volet)',
                        [
                            'repere' => $ligneCommandeData['Repere'],
                            'nbre' => $ligneCommandeData['Nbre'],
                            'largeur' => $ligneCommandeData['Largeur_(LA)'],
                            'hauteur' => $ligneCommandeData['Hauteur_(HC)'],
                            'at' => $ligneCommandeData['AT'],
                            'b1' => $ligneCommandeData['B1'],
                            'b2' => $ligneCommandeData['B2'],
                            's1' => $ligneCommandeData['S1'],
                            's2' => $ligneCommandeData['S2'],
                            'angle' => $ligneCommandeData['Angle'],
                            'elargisseur' => $ligneCommandeData['Elargisseur_coulisse'],
                            'cable' => $ligneCommandeData['Câble_longueur_utile_5m'],
                            'panneau' => $ligneCommandeData['Panneau_PV_deporte'],
                            'type_coulisse' => $ligneCommandeData['type_coulisse_id'],
                            'conf_volet' => $confVoletId,
                        ]
                    );
                }
            }

            $this->addFlash('success', 'Configuration enregistrée.');
            return $this->redirectToRoute('app_configuration_volet_commande', ['projet' => $projet->getId()]);
        }

        // --- Chargement données existantes conf_teinte_tablier ---
        $confTeinteTablierExistant = null;
        $lignesCommandeExistantes = [];
        $confVoletData = null;
        $confVolet = $projet->getConfVolet();
        if ($confVolet !== null) {
            try {
                $confVoletData = $connection->fetchAssociative(
                    'SELECT * FROM conf_volet WHERE id = :id',
                    ['id' => $confVolet->getId()]
                ) ?: null;
            } catch (\Exception) {
                $confVoletData = null;
            }

            try {
                $confTeinteTablierExistant = $connection->fetchAssociative(
                    'SELECT * FROM conf_teinte_tablier WHERE conf_volet_id = :cv',
                    ['cv' => $confVolet->getId()]
                ) ?: null;
            } catch (\Exception) {
                $confTeinteTablierExistant = null;
            }

            try {
                $lignesCommandeExistantes = $connection->fetchAllAssociative(
                    'SELECT * FROM `Lignes_de_commande_BLOC_N_R_iD4` WHERE conf_volet_id = :cv ORDER BY id',
                    ['cv' => $confVolet->getId()]
                ) ?: [];
            } catch (\Exception) {
                $lignesCommandeExistantes = [];
            }
        }
        
        $extensionOffreActive = false;
        if ($confVoletData !== null) {
            $rawExtensionOffre = $confVoletData['Extension_offre'] ?? null;

            if (is_string($rawExtensionOffre)) {
                $extensionOffreActive = $rawExtensionOffre === "\x01" || (is_numeric($rawExtensionOffre) && (int) $rawExtensionOffre === 1);
            } elseif (is_int($rawExtensionOffre)) {
                $extensionOffreActive = $rawExtensionOffre === 1;
            } elseif (is_bool($rawExtensionOffre)) {
                $extensionOffreActive = $rawExtensionOffre;
            }
        }
        
        // Caissons PVC
        $caissons = $connection->fetchAllAssociative('SELECT * FROM Caisson_PVC ORDER BY bloc');

        // Tablier
        try {
            $tabliers = $connection->fetchAllAssociative('SELECT * FROM Tablier ORDER BY `type`');
        } catch (\Exception $e) {
            $tabliers = [];
        }
        
        // Teintes tablier volet
        try {
            $nuanciersStandard = $connection->fetchAllAssociative('SELECT * FROM nuancier_standard ORDER BY id');
        } catch (\Exception $e) {
            $nuanciersStandard = [];
        }
        
        // Spécificités caisson
        try {
            $specificitesCaisson = $connection->fetchAllAssociative('SELECT * FROM Specificites_caisson ORDER BY id');
            $binaryToBool = static function (mixed $value): bool {
                if ($value === null) {
                    return false;
                }

                if (is_bool($value)) {
                    return $value;
                }

                if (is_int($value)) {
                    return $value === 1;
                }

                if (is_string($value)) {
                    if ($value === "\x01") {
                        return true;
                    }

                    if ($value === "\x00" || $value === '') {
                        return false;
                    }

                    if (is_numeric($value)) {
                        return (int) $value === 1;
                    }

                    return ord($value[0]) === 1;
                }

                return false;
            };

            foreach ($specificitesCaisson as &$specificiteCaisson) {
                $faceExterieureAlu = $binaryToBool($specificiteCaisson['Face_exterieure_alu'] ?? null);
                $phtN = $binaryToBool($specificiteCaisson['PHT_N'] ?? null);
                $phtR = $binaryToBool($specificiteCaisson['PHT_R'] ?? null);
                $optionAutreTeinte = trim((string) ($specificiteCaisson['Option_autre_teinte'] ?? ''));

                $specificiteCaisson['face_exterieure_alu_bool'] = $faceExterieureAlu;
                $specificiteCaisson['pht_n_bool'] = $phtN;
                $specificiteCaisson['pht_r_bool'] = $phtR;
                $specificiteCaisson['option_autre_teinte_text'] = $optionAutreTeinte;

                $specificiteCaisson['display_label'] = sprintf(
                    'Face ext. alu: %s | Option autre teinte: %s | PHT N: %s | PHT R: %s',
                    $faceExterieureAlu ? 'Oui' : 'Non',
                    $optionAutreTeinte !== '' ? $optionAutreTeinte : '-',
                    $phtN ? 'Oui' : 'Non',
                    $phtR ? 'Oui' : 'Non'
                );
            }
            unset($specificiteCaisson);
        } catch (\Exception $e) {
            $specificitesCaisson = [];
        }
        
        // Options moteur radio Bubendorff
        try {
            $moteursRadio = $connection->fetchAllAssociative('SELECT * FROM Options_Moteur_Radio_Bubendorff ORDER BY id');
            $binaryToBool = static function (mixed $value): bool {
                if ($value === null) {
                    return false;
                }

                if (is_bool($value)) {
                    return $value;
                }

                if (is_int($value)) {
                    return $value === 1;
                }

                if (is_string($value)) {
                    if ($value === "\x01") {
                        return true;
                    }

                    if ($value === "\x00" || $value === '') {
                        return false;
                    }

                    if (is_numeric($value)) {
                        return (int) $value === 1;
                    }

                    return ord($value[0]) === 1;
                }

                return false;
            };

            foreach ($moteursRadio as &$moteurRadio) {
                $cmg = isset($moteurRadio['CMG_groupe_CLIMAT+']) ? (int) $moteurRadio['CMG_groupe_CLIMAT+'] : null;
                $h4c = $binaryToBool($moteurRadio['H4C_Horloge_4_canaux'] ?? null);
                $dia = $binaryToBool($moteurRadio['DIA_iDiamant'] ?? null);
                $smu = $binaryToBool($moteurRadio['SMU_Support_mural_émetteur_3_boutons'] ?? null);

                $moteurRadio['cmg_groupe_climat_plus_int'] = $cmg;
                $moteurRadio['h4c_horloge_4_canaux_bool'] = $h4c;
                $moteurRadio['dia_idiamant_bool'] = $dia;
                $moteurRadio['smu_support_mural_3_boutons_bool'] = $smu;
                $moteurRadio['display_label'] = sprintf(
                    'CMG CLIMAT+: %s | H4C: %s | DIA: %s | SMU 3 boutons: %s',
                    $cmg !== null ? (string) $cmg : '-',
                    $h4c ? 'Oui' : 'Non',
                    $dia ? 'Oui' : 'Non',
                    $smu ? 'Oui' : 'Non'
                );
            }
            unset($moteurRadio);
        } catch (\Exception $e) {
            $moteursRadio = [];
        }
        
        // Options moteur filaire Bubendorff
        try {
            $moteursFilaire = $connection->fetchAllAssociative('SELECT * FROM `Option Moteur-Filaire_Bubendorff` ORDER BY id');
            $binaryToBool = static function (mixed $value): bool {
                if ($value === null) {
                    return false;
                }

                if (is_bool($value)) {
                    return $value;
                }

                if (is_int($value)) {
                    return $value === 1;
                }

                if (is_string($value)) {
                    if ($value === "\x01") {
                        return true;
                    }

                    if ($value === "\x00" || $value === '') {
                        return false;
                    }

                    if (is_numeric($value)) {
                        return (int) $value === 1;
                    }

                    return ord($value[0]) === 1;
                }

                return false;
            };

            foreach ($moteursFilaire as &$moteurFilaire) {
                $invAvecInverseur = $binaryToBool($moteurFilaire['INV_avec_inverseur'] ?? null);
                $moteurFilaire['inv_avec_inverseur_bool'] = $invAvecInverseur;
                $moteurFilaire['display_label'] = sprintf(
                    'INV avec inverseur: %s',
                    $invAvecInverseur ? 'Oui' : 'Non'
                );
            }
            unset($moteurFilaire);
        } catch (\Exception $e) {
            $moteursFilaire = [];
        }
        
        // Options pack SAV
        try {
            $packsSAV = $connection->fetchAllAssociative('SELECT * FROM Option_pack_SAV ORDER BY id');
        } catch (\Exception $e) {
            $packsSAV = [];
        }
        
        // Lignes de commande BLOC N / R iD4
        try {
            $lignesCommande = $connection->fetchAllAssociative('SELECT * FROM Lignes_de_commande_BLOC_N_R_iD4 ORDER BY id');
        } catch (\Exception $e) {
            $lignesCommande = [];
        }

        // Types de coulisse
        try {
            $typesCoulisse = $connection->fetchAllAssociative('SELECT * FROM type_coulisse ORDER BY id');
        } catch (\Exception $e) {
            $typesCoulisse = [];
        }

        // Teinte encadrement elargi (FK de teinte_encadrement)
        try {
            $teinteEncadrementElargi = $connection->fetchAllAssociative('SELECT * FROM teinte_encadrement_elargi ORDER BY id');
        } catch (\Exception $e) {
            $teinteEncadrementElargi = [];
        }

        // Teinte encadrement specifique (FK de teinte_encadrement)
        try {
            $teinteEncadrementSpecifique = $connection->fetchAllAssociative('SELECT * FROM teinte_encadrement_specifique ORDER BY id');
        } catch (\Exception $e) {
            $teinteEncadrementSpecifique = [];
        }

        // Nuancier standard (FK de teinte_encadrement)
        try {
            $nuancierStandard = $connection->fetchAllAssociative('SELECT * FROM nuancier_standard ORDER BY id');
        } catch (\Exception $e) {
            $nuancierStandard = [];
        }

        return $this->render($this->resolveVoletCommandeTemplate($gammeEntity), [
            'projet' => $projet,
            'gamme_id4' => $voletConfig['gamme_id4'],
            'gamme_selectionnee_nom' => $gammeSelectionneeNom,
            'extension_offre_active' => $extensionOffreActive,
            'caissons' => $caissons,
            'tabliers' => $tabliers,
            'nuanciers_standard' => $nuanciersStandard,
            'teinte_encadrement_elargi' => $teinteEncadrementElargi,
            'teinte_encadrement_specifique' => $teinteEncadrementSpecifique,
            'nuancier_standard' => $nuancierStandard,
            'specificites_caisson' => $specificitesCaisson,
            'moteurs_radio' => $moteursRadio,
            'moteurs_filaire' => $moteursFilaire,
            'packs_sav' => $packsSAV,
            'lignes_commande' => $lignesCommande,
            'lignes_commande_existantes' => $lignesCommandeExistantes,
            'conf_volet_data' => $confVoletData,
            'types_coulisse' => $typesCoulisse,
            'conf_teinte_tablier' => $confTeinteTablierExistant,
        ]);
    }

    private function resolveVoletCommandeTemplate(?GammeVolet $gammeVolet): string
    {
        if ($gammeVolet === null || $gammeVolet->getNom() === null) {
            return 'configuration/volet/commande/default.html.twig';
        }

        $gammeKey = $this->normalizeGammeName((string) $gammeVolet->getNom());

        return self::VOLET_COMMANDE_TEMPLATE_MAP[$gammeKey] ?? 'configuration/volet/commande/default.html.twig';
    }

    private function normalizeGammeName(string $value): string
    {
        $asciiValue = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $normalized = strtolower($asciiValue !== false ? $asciiValue : $value);
        $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized);

        return trim((string) $normalized, '-');
    }

    /**
     * Gérer les couleurs spéciales depuis le formulaire moderne
     */
    /**
     * Gérer les couleurs spéciales (blanc et crème) - méthode legacy
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
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic', 'image/heif'];
            if (!in_array($fileMimeType, $allowedMimes)) {
                $errorDetails[] = "Fichier $index ($originalName): type MIME non autorisé ($fileMimeType)";
                continue;
            }

            // Valider la taille avec limite de 12 MO
            $appMaxSize = 12 * 1024 * 1024; // 12 MO
            $maxUploadSize = min(
                $appMaxSize,
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
                // Déplacer le fichier temporairement
                $uploadedFile->move($uploadsDirectory, $fileName);
                
                // Compresser l'image pour réduire sa taille à moins de 1 MO
                $filePath = $uploadsDirectory . '/' . $fileName;
                $this->compressImage($filePath, $fileExtension);
                
                // Recalculer la taille après compression
                $compressedSize = filesize($filePath);
                
                // Ajouter les métadonnées de la photo
                $photoTitle = isset($titles[$index]) && !empty(trim($titles[$index])) 
                    ? trim($titles[$index]) 
                    : $originalFilename;
                    
                $metadata[$fileName] = [
                    'title' => $photoTitle,
                    'originalName' => $originalName,
                    'uploadDate' => date('Y-m-d H:i:s'),
                    'size' => $compressedSize,
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
            'heic' => 'image/heic',
            'heif' => 'image/heif',
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

    #[Route('/pf/{projet}/pdf/{confpf}', name: 'app_configuration_pf_pdf', methods: ['GET', 'POST'], requirements: ['confpf' => '\d+'])]
    public function configurePfPdf(Projet $projet, int $confpf, Request $request, \App\Service\PdfGeneratorService $pdfGenerator, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la configuration ConfPf
        $confPf = $entityManager->getRepository(ConfPf::class)->find($confpf);
        if (!$confPf) {
            throw $this->createNotFoundException('Configuration non trouvée.');
        }
        
        // Vérifier que la configuration appartient bien au projet
        if ($confPf->getProjet()->getId() !== $projet->getId()) {
            throw $this->createNotFoundException('Configuration non trouvée pour ce projet.');
        }

        $pdfPath = null;
        $encodedPdfPath = null;
        
        // Récupérer les schémas PDF disponibles
        $availableSchemas = $this->entityManager->getRepository(\App\Entity\PdfSchema::class)
            ->findActiveOrderedByOrdre();
            
        if (empty($availableSchemas)) {
            $this->addFlash('error', 'Aucun schéma PDF disponible. Contactez l\'administrateur.');
        }
        
        // Récupérer les PDFs existants pour ce projet
        $existingPdfs = [];
        $projetPdfs = $this->entityManager->getRepository(\App\Entity\ProjetPdf::class)
            ->findByProjetOrderedByDate($projet);
        
        foreach ($projetPdfs as $projetPdf) {
            // Vérifier que le fichier existe encore
            $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $projetPdf->getFilePath();
            if (file_exists($fullPath)) {
                $existingPdfs[] = [
                    'id' => $projetPdf->getId(),
                    'filename' => $projetPdf->getFileName(),
                    'path' => $projetPdf->getFilePath(),
                    'encodedPath' => $projetPdf->getEncodedPath(),
                    'customValue' => $projetPdf->getCustomValue(),
                    'createdAt' => $projetPdf->getCreatedAt(),
                    'formattedSize' => $projetPdf->getFormattedSize(),
                ];
            }
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            
            if ($action === 'save_and_return') {
                // Action : sauvegarder et retourner au projet
                $this->addFlash('success', 'Configuration finalisée avec succès !');
                return $this->redirectToRoute('app_projet_show', ['id' => $projet->getId()]);
            }
            
            if ($action === 'generate_pdf') {
                // Récupérer le schéma sélectionné
                $schemaId = (int) $request->request->get('pdf_schema_id');
                
                if (!$schemaId) {
                    $this->addFlash('error', 'Veuillez sélectionner un schéma avant de générer le PDF.');
                } else {
                    // Vérifier que le schéma existe et est actif
                    $pdfSchema = $this->entityManager->getRepository(\App\Entity\PdfSchema::class)
                        ->findActiveById($schemaId);
                        
                    if (!$pdfSchema) {
                        $this->addFlash('error', 'Schéma PDF invalide ou inactif.');
                    } else {
                        try {
                            $customValue = 0;
                            $calculatedValue = 0;
                            $successMessage = '';
                            
                            // Gérer différemment selon le type de schéma
                            if ($pdfSchema->getNom() === 'Pose en applique sans tapées') {
                                // Pour pose en applique sans tapées : récupérer largeur et hauteur
                                $largeurTableau = (float) $request->request->get('largeur_tableau');
                                $hauteurTableau = (float) $request->request->get('hauteur_tableau');
                                $largeurFabrication = (float) $request->request->get('largeur_fabrication');
                                $hauteurFabrication = (float) $request->request->get('hauteur_fabrication');
                                
                                if ($largeurTableau <= 0 || $hauteurTableau <= 0) {
                                    $this->addFlash('error', 'Veuillez saisir des dimensions valides pour les tableaux finis.');
                                    return $this->redirectToRoute('app_configuration_pf_pdf', [
                                        'projet' => $projet->getId(),
                                        'confpf' => $confPf->getId()
                                    ]);
                                }
                                
                                // Vérifier que les dimensions de fabrication sont correctement calculées
                                $expectedLargeurFab = $largeurTableau - 10;
                                $expectedHauteurFab = $hauteurTableau - 10;
                                if (abs($largeurFabrication - $expectedLargeurFab) > 0.1) {
                                    $largeurFabrication = $expectedLargeurFab; // Recalculer pour sécurité
                                }
                                if (abs($hauteurFabrication - $expectedHauteurFab) > 0.1) {
                                    $hauteurFabrication = $expectedHauteurFab; // Recalculer pour sécurité
                                }
                                
                                // Utiliser la largeur comme valeur principale pour la compatibilité
                                $customValue = $largeurTableau;
                                $calculatedValue = $hauteurTableau; // Stocker la hauteur dans calculatedValue
                                
                                $successMessage = 'PDF généré avec succès - Largeur Tableaux: ' . number_format($largeurTableau, 0) . ' mm, Hauteur: ' . number_format($hauteurTableau, 0) . ' mm, Largeur Fabrication: ' . number_format($largeurFabrication, 0) . ' mm, Hauteur Fabrication: ' . number_format($hauteurFabrication, 0) . ' mm';
                            } elseif ($pdfSchema->getNom() === 'Pose tunnelle') {
                                // Pour pose tunnelle : récupérer largeur et hauteur
                                $largeurTableau = (float) $request->request->get('largeur_tableau_tunnelle');
                                $hauteurTableau = (float) $request->request->get('hauteur_tableau_tunnelle');
                                $largeurFabrication = (float) $request->request->get('largeur_fabrication_tunnelle');
                                $hauteurFabrication = (float) $request->request->get('hauteur_fabrication_tunnelle');
                                
                                if ($largeurTableau <= 0 || $hauteurTableau <= 0) {
                                    $this->addFlash('error', 'Veuillez saisir des dimensions valides pour les tableaux finis.');
                                    return $this->redirectToRoute('app_configuration_pf_pdf', [
                                        'projet' => $projet->getId(),
                                        'confpf' => $confPf->getId()
                                    ]);
                                }
                                
                                // Vérifier que les dimensions de fabrication sont correctement calculées
                                $expectedLargeurFab = $largeurTableau + 60;
                                $expectedHauteurFab = $hauteurTableau + 30;
                                if (abs($largeurFabrication - $expectedLargeurFab) > 0.1) {
                                    $largeurFabrication = $expectedLargeurFab; // Recalculer pour sécurité
                                }
                                if (abs($hauteurFabrication - $expectedHauteurFab) > 0.1) {
                                    $hauteurFabrication = $expectedHauteurFab; // Recalculer pour sécurité
                                }
                                
                                // Utiliser la largeur comme valeur principale pour la compatibilité
                                $customValue = $largeurTableau;
                                $calculatedValue = $hauteurTableau; // Stocker la hauteur dans calculatedValue
                                
                                $successMessage = 'PDF généré avec succès - Largeur Tableaux: ' . number_format($largeurTableau, 0) . ' mm, Hauteur: ' . number_format($hauteurTableau, 0) . ' mm, Largeur Fabrication: ' . number_format($largeurFabrication, 0) . ' mm, Hauteur Fabrication: ' . number_format($hauteurFabrication, 0) . ' mm';
                            } elseif ($pdfSchema->getNom() === 'Pose applique avec tapées isolation') {
                                // Pour pose applique avec tapées isolation : récupérer largeur, hauteur et les deux tapées
                                $largeurTableau = (float) $request->request->get('largeur_tableau_tapees');
                                $hauteurTableau = (float) $request->request->get('hauteur_tableau_tapees');
                                $tapeesLargeur = (float) $request->request->get('tapees_largeur');
                                $tapeesHauteur = (float) $request->request->get('tapees_hauteur');
                                
                                // Validation détaillée
                                $errors = [];
                                if ($largeurTableau <= 0) $errors[] = 'Largeur tableaux finis';
                                if ($hauteurTableau <= 0) $errors[] = 'Hauteur tableaux finis';
                                if ($tapeesLargeur <= 0) $errors[] = 'Tapées largeur';
                                if ($tapeesHauteur <= 0) $errors[] = 'Tapées hauteur';
                                
                                if (!empty($errors)) {
                                    $this->addFlash('error', 'Champs manquants ou invalides : ' . implode(', ', $errors) . '. Veuillez remplir tous les champs requis.');
                                    return $this->redirectToRoute('app_configuration_pf_pdf', [
                                        'projet' => $projet->getId(),
                                        'confpf' => $confPf->getId()
                                    ]);
                                }
                                
                                // Calculer la largeur de fabrication (largeur tableaux + 58)
                                $largeurFabrication = $largeurTableau + 58;
                                
                                // Calculer la hauteur de fabrication (hauteur tableaux + 29)
                                $hauteurFabrication = $hauteurTableau + 29;
                                
                                // Stocker les valeurs dans additionalValues
                                $additionalValues = [
                                    'largeur_tableau' => $largeurTableau,
                                    'hauteur_tableau' => $hauteurTableau,
                                    'tapees_largeur' => $tapeesLargeur,
                                    'tapees_hauteur' => $tapeesHauteur,
                                    'largeur_fabrication' => $largeurFabrication,
                                    'hauteur_fabrication' => $hauteurFabrication,
                                ];
                                
                                // Utiliser la largeur comme valeur principale pour la compatibilité
                                $customValue = $largeurTableau;
                                $calculatedValue = $hauteurTableau; // Stocker la hauteur dans calculatedValue
                                
                                $successMessage = 'PDF généré avec succès - Largeur Tableaux: ' . number_format($largeurTableau, 0) . ' mm, Hauteur: ' . number_format($hauteurTableau, 0) . ' mm, Tapées Largeur: ' . number_format($tapeesLargeur, 0) . ' mm, Tapées Hauteur: ' . number_format($tapeesHauteur, 0) . ' mm, Largeur Fabrication: ' . number_format($largeurFabrication, 0) . ' mm, Hauteur Fabrication: ' . number_format($hauteurFabrication, 0) . ' mm';
                            } elseif ($pdfSchema->getNom() === 'Pose en rénovation') {
                                // Pour pose en rénovation : récupérer largeur et hauteur dormant bois + particularité
                                $largeurDormantBois = (float) $request->request->get('largeur_dormant_bois');
                                $hauteurDormantBois = (float) $request->request->get('hauteur_dormant_bois');
                                $particulariteCompensateur = $request->request->get('particularite_compensateur') ? true : false;
                                
                                if ($largeurDormantBois <= 0 || $hauteurDormantBois <= 0) {
                                    $this->addFlash('error', 'Veuillez saisir des valeurs valides pour les dimensions entre dormants bois.');
                                    return $this->redirectToRoute('app_configuration_pf_pdf', [
                                        'projet' => $projet->getId(),
                                        'confpf' => $confPf->getId()
                                    ]);
                                }
                                
                                // Calculer la largeur et hauteur de fabrication (dormant bois - 10)
                                $largeurFabrication = $largeurDormantBois - 10;
                                $hauteurFabrication = $hauteurDormantBois - 10;
                                
                                // Stocker les valeurs dans additionalValues
                                $additionalValues = [
                                    'largeur_dormant_bois' => $largeurDormantBois,
                                    'hauteur_dormant_bois' => $hauteurDormantBois,
                                    'particularite_compensateur' => $particulariteCompensateur,
                                    'largeur_fabrication' => $largeurFabrication,
                                    'hauteur_fabrication' => $hauteurFabrication,
                                ];
                                
                                // Utiliser la largeur comme valeur principale pour la compatibilité
                                $customValue = $largeurDormantBois;
                                $calculatedValue = $hauteurDormantBois;
                                
                                $successMessage = 'PDF généré avec succès - Largeur entre dormant bois: ' . number_format($largeurDormantBois, 0) . ' mm, Hauteur dormant bois: ' . number_format($hauteurDormantBois, 0) . ' mm, Largeur Fabrication: ' . number_format($largeurFabrication, 0) . ' mm, Hauteur Fabrication: ' . number_format($hauteurFabrication, 0) . ' mm' . ($particulariteCompensateur ? ', Particularité compensateur 10mm activée' : '');
                            } else {
                                // Pour les autres schémas : logique standard
                                $customValue = (float) $request->request->get('custom_value');
                                $calculatedValue = (float) $request->request->get('calculated_value');
                                
                                if ($customValue <= 0) {
                                    $this->addFlash('error', 'Veuillez saisir une côte interne valide.');
                                    return $this->redirectToRoute('app_configuration_pf_pdf', [
                                        'projet' => $projet->getId(),
                                        'confpf' => $confPf->getId()
                                    ]);
                                }
                                
                                $successMessage = 'PDF généré avec succès - Côte interne: ' . number_format($customValue, 0) . ' mm, Côte extérieur: ' . number_format($calculatedValue, 0) . ' mm';
                            }
                            
                            // Préparer les valeurs additionnelles pour les schémas spécifiques
                            $additionalValues = null;
                            if ($pdfSchema->getNom() === 'Pose en applique sans tapées' || $pdfSchema->getNom() === 'Pose tunnelle') {
                                $additionalValues = [
                                    'largeur_tableau' => $largeurTableau,
                                    'hauteur_tableau' => $hauteurTableau,
                                    'largeur_fabrication' => $largeurFabrication,
                                    'hauteur_fabrication' => $hauteurFabrication
                                ];
                            } elseif ($pdfSchema->getNom() === 'Pose applique avec tapées isolation') {
                                $additionalValues = [
                                    'largeur_tableau' => $largeurTableau,
                                    'hauteur_tableau' => $hauteurTableau,
                                    'tapees_largeur' => $tapeesLargeur,
                                    'tapees_hauteur' => $tapeesHauteur,
                                    'largeur_fabrication' => $largeurFabrication,
                                    'hauteur_fabrication' => $hauteurFabrication
                                ];
                            } elseif ($pdfSchema->getNom() === 'Pose en rénovation') {
                                $additionalValues = [
                                    'largeur_dormant_bois' => $largeurDormantBois,
                                    'hauteur_dormant_bois' => $hauteurDormantBois,
                                    'particularite_compensateur' => $particulariteCompensateur,
                                    'largeur_fabrication' => $largeurFabrication,
                                    'hauteur_fabrication' => $hauteurFabrication,
                                ];
                            }
                            
                            // Sauvegarder le type de pose dans la configuration
                            $confPf->setPoseType($pdfSchema->getNom());
                            $this->entityManager->persist($confPf);
                            $this->entityManager->flush();
                            
                            // Générer le PDF avec le schéma sélectionné
                            $pdfPath = $pdfGenerator->generatePlanPdf($confPf, $customValue, $calculatedValue, $pdfSchema, $additionalValues);
                            $encodedPdfPath = base64_encode($pdfPath);
                            
                            $this->addFlash('success', $successMessage . ' avec le schéma "' . $pdfSchema->getNom() . '"');
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
                        }
                    }
                }
            }
        }

        return $this->render('configuration/pdf_generation.html.twig', [
            'projet' => $projet,
            'confPf' => $confPf,
            'pdfPath' => $pdfPath,
            'encodedPdfPath' => $encodedPdfPath,
            'customValue' => $request->request->get('custom_value'),
            'existingPdfs' => $existingPdfs,
            'availableSchemas' => $availableSchemas,
            'selectedSchemaId' => $request->request->get('pdf_schema_id'),
        ]);
    }

    #[Route('/pf/{projet}/pdf/preview', name: 'app_configuration_pf_pdf_preview', methods: ['GET'])]
    public function previewPdf(Projet $projet, Request $request): Response
    {
        $encodedPath = $request->query->get('path');
        if (!$encodedPath) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }

        $pdfPath = base64_decode($encodedPath);
        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $pdfPath;

        if (!file_exists($fullPath)) {
            throw $this->createNotFoundException('Fichier PDF non trouvé.');
        }

        // Retourner le PDF avec les en-têtes appropriés pour l'aperçu
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="apercu_plan.pdf"');
        $response->setContent(file_get_contents($fullPath));

        return $response;
    }

    #[Route('/pf/{projet}/pdf/download', name: 'app_configuration_pf_pdf_download', methods: ['GET'])]
    public function downloadPdf(Projet $projet, Request $request): Response
    {
        $encodedPath = $request->query->get('path');
        if (!$encodedPath) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }

        $pdfPath = base64_decode($encodedPath);
        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $pdfPath;

        if (!file_exists($fullPath)) {
            throw $this->createNotFoundException('Fichier PDF non trouvé.');
        }

        $fileName = 'plan_projet_' . $projet->getRefClient() . '_' . date('Ymd') . '.pdf';

        return $this->file($fullPath, $fileName);
    }

    /**
     * Compresse une image pour réduire sa taille à moins de 1 MO
     */
    private function compressImage(string $filePath, string $extension): void
    {
        $targetSize = 1024 * 1024; // 1 MO en bytes
        $currentSize = filesize($filePath);
        
        // Si le fichier fait déjà moins de 1 MO, ne pas le compresser
        if ($currentSize <= $targetSize) {
            return;
        }

        $extension = strtolower($extension);
        
        // Gestion spéciale pour HEIC/HEIF (conversion en JPEG)
        if (in_array($extension, ['heic', 'heif'])) {
            // Pour l'instant, renommer le fichier en .jpg car la plupart des navigateurs
            // ne supportent pas HEIC et PHP n'a pas de support natif
            $newPath = str_replace(['.' . $extension], '.jpg', $filePath);
            if (rename($filePath, $newPath)) {
                $filePath = $newPath;
                $extension = 'jpg';
            } else {
                return; // Impossible de renommer le fichier
            }
        }
        
        // Créer l'image selon le type
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                return; // Type non supporté pour la compression
        }

        if (!$image) {
            return; // Erreur lors du chargement de l'image
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // Commencer avec une qualité de 85% et réduire si nécessaire
        $quality = 85;
        $scale = 1.0;
        
        do {
            // Calculer les nouvelles dimensions
            $newWidth = (int)($originalWidth * $scale);
            $newHeight = (int)($originalHeight * $scale);
            
            // Redimensionner l'image si nécessaire
            if ($scale < 1.0) {
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Préserver la transparence pour PNG et GIF
                if ($extension === 'png' || $extension === 'gif') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    imagefill($resizedImage, 0, 0, $transparent);
                }
                
                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                imagedestroy($image);
                $image = $resizedImage;
            }
            
            // Sauvegarder avec la qualité actuelle
            ob_start();
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, null, $quality);
                    break;
                case 'png':
                    // PNG: qualité va de 0 à 9 (inversée)
                    $pngQuality = (int)((100 - $quality) / 11.11);
                    imagepng($image, null, $pngQuality);
                    break;
                case 'gif':
                    imagegif($image, null);
                    break;
                case 'webp':
                    imagewebp($image, null, $quality);
                    break;
            }
            $imageData = ob_get_contents();
            ob_end_clean();
            
            $newSize = strlen($imageData);
            
            // Si la taille est acceptable, sauvegarder le fichier
            if ($newSize <= $targetSize) {
                file_put_contents($filePath, $imageData);
                imagedestroy($image);
                return;
            }
            
            // Réduire la qualité ou la taille pour la prochaine itération
            if ($quality > 20) {
                $quality -= 10;
            } else {
                $scale -= 0.1;
                $quality = 85; // Reset quality when scaling
            }
            
        } while ($quality > 10 && $scale > 0.2); // Limites de sécurité
        
        // Sauvegarder même si on n'a pas atteint la taille cible
        file_put_contents($filePath, $imageData);
        imagedestroy($image);
    }
}