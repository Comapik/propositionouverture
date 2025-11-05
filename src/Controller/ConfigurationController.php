<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Produit;
use App\Entity\ConfPf;
use App\Form\ProductSelectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
                    // Note: We'll need to add an ouverture field to ConfPf entity later
                    // For now, we'll store it in notes or add the field
                    $confPf->setNotes($confPf->getNotes() . "\nOuverture: " . $ouverture->getNom());
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
        return $this->render('configuration/pf_details.html.twig', [
            'projet' => $projet,
            'confpf' => $confPf,
            'produit' => $confPf->getProduit(),
            'categorie' => $confPf->getCategorie(),
            'sousCategorie' => $confPf->getSousCategorie(),
        ]);
    }

    #[Route('/volet/{projet}', name: 'app_configuration_volet', methods: ['GET', 'POST'])]
    public function configureVolet(Projet $projet, Request $request): Response
    {
        // TODO: Implement shutter configuration
        return $this->render('configuration/volet.html.twig', [
            'projet' => $projet,
        ]);
    }
}