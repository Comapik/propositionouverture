<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\SousCategorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * ProduitFixtures following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Loads product test data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized test data
 * Following KISS principle: Simple data loading
 */
class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer les produits principaux
        $produits = [
            'Portes' => [
                'categories' => [
                    'Portes d\'entrée' => ['Portes blindées', 'Portes vitrées', 'Portes en bois'],
                    'Portes intérieures' => ['Portes coulissantes', 'Portes battantes'],
                ],
            ],
            'Fenêtres' => [
                'categories' => [
                    'Fenêtres PVC' => ['Fenêtres fixes', 'Fenêtres oscillo-battantes', 'Fenêtres coulissantes'],
                    'Fenêtres Aluminium' => ['Fenêtres à frappe', 'Fenêtres coulissantes alu'],
                    'Fenêtres Bois' => ['Fenêtres traditionnelles', 'Fenêtres mixtes bois-alu'],
                ],
            ],
            'Portes-Fenêtres' => [
                'categories' => [
                    'Portes-fenêtres PVC' => ['2 vantaux', '3 vantaux', '4 vantaux'],
                    'Portes-fenêtres Alu' => ['Galandage', 'Coulissante', 'Battante'],
                ],
            ],
            'Baies vitrées' => [
                'categories' => [
                    'Baies coulissantes' => ['2 rails', '3 rails', '4 rails'],
                    'Baies fixes' => ['Simple vitrage', 'Double vitrage', 'Triple vitrage'],
                ],
            ],
        ];

        foreach ($produits as $produitNom => $produitData) {
            // Créer le produit
            $produit = new Produit();
            $produit->setNom($produitNom);
            $manager->persist($produit);

            // Créer les catégories
            foreach ($produitData['categories'] as $categorieNom => $sousCategories) {
                $categorie = new Categorie();
                $categorie->setNom($categorieNom);
                $categorie->setProduit($produit);
                $manager->persist($categorie);

                // Créer les sous-catégories
                foreach ($sousCategories as $sousCategorieNom) {
                    $sousCategorie = new SousCategorie();
                    $sousCategorie->setNom($sousCategorieNom);
                    $sousCategorie->setProduit($produit);
                    $sousCategorie->setCategorie($categorie);
                    $manager->persist($sousCategorie);
                }
            }
        }

        $manager->flush();
    }
}