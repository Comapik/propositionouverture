<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Produit;
use App\Entity\ConfPf;
use App\Entity\Fournisseur;
use App\Form\ProductSelectionType;
use App\Form\ConfPfDetailsType;
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
        private readonly EntityManagerInterface $entityManager
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

        $form = $this->createForm(ConfPfDetailsType::class, $confPf, [
            'fournisseurs' => $fournisseurs,
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
}