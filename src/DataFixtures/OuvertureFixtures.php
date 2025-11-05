<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Ouverture;
use App\Entity\SousCategorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * OuvertureFixtures following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Loads opening types test data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized test data
 * Following KISS principle: Simple data loading
 */
class OuvertureFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer quelques sous-catégories existantes
        $sousCategories = $manager->getRepository(SousCategorie::class)->findAll();
        
        if (empty($sousCategories)) {
            return; // Pas de sous-catégories, on ne peut pas créer d'ouvertures
        }

        // Types d'ouverture pour fenêtres
        $typesOuverture = [
            'Fenêtre fixe',
            'Fenêtre oscillo-battante',
            'Fenêtre coulissante',
            'Fenêtre à soufflet',
            'Fenêtre à frappe',
            'Fenêtre pivotante',
            'Fenêtre à guillotine',
            'Porte-fenêtre 1 vantail',
            'Porte-fenêtre 2 vantaux',
            'Porte-fenêtre 3 vantaux',
            'Porte-fenêtre coulissante',
            'Baie vitrée coulissante',
            'Baie vitrée à galandage',
            'Fenêtre de toit',
            'Velux',
            'Lucarne',
            'Bow-window',
            'Oriel',
            'Imposte fixe',
            'Imposte ouvrante',
        ];

        foreach ($typesOuverture as $index => $typeNom) {
            $ouverture = new Ouverture();
            $ouverture->setNom($typeNom);
            
            // Assigner aléatoirement à une sous-catégorie
            $sousCategorie = $sousCategories[$index % count($sousCategories)];
            $ouverture->setSousCategorie($sousCategorie);
            
            $manager->persist($ouverture);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProduitFixtures::class,
        ];
    }
}