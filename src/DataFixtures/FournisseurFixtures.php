<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Fournisseur;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * FournisseurFixtures following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Loads supplier test data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized test data creation
 * Following KISS principle: Simple fixture loading
 */
class FournisseurFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get existing products from database
        $produits = $manager->getRepository(Produit::class)->findAll();
        
        if (empty($produits)) {
            // If no products exist, create some basic ones
            return;
        }

        $fournisseursData = [
            // Portes/Fenêtres PVC
            ['marque' => 'Schüco', 'produit_nom' => 'PVC'],
            ['marque' => 'VEKA', 'produit_nom' => 'PVC'],
            ['marque' => 'Rehau', 'produit_nom' => 'PVC'],
            ['marque' => 'Profialis', 'produit_nom' => 'PVC'],
            
            // Bois
            ['marque' => 'Kömmerling', 'produit_nom' => 'Bois'],
            ['marque' => 'Internorm', 'produit_nom' => 'Bois'],
            ['marque' => 'Bois Noble', 'produit_nom' => 'Bois'],
            
            // Aluminium
            ['marque' => 'Technal', 'produit_nom' => 'Aluminium'],
            ['marque' => 'K-Line', 'produit_nom' => 'Aluminium'],
            ['marque' => 'Reynaers', 'produit_nom' => 'Aluminium'],
            ['marque' => 'Sapa', 'produit_nom' => 'Aluminium'],
            
            // Bois/Alu
            ['marque' => 'Internorm', 'produit_nom' => 'Bois/Alu'],
            ['marque' => 'Schüco', 'produit_nom' => 'Bois/Alu'],
            ['marque' => 'Kömmerling', 'produit_nom' => 'Bois/Alu'],
        ];

        foreach ($fournisseursData as $data) {
            // Find product by name
            $produit = null;
            foreach ($produits as $p) {
                if (strpos(strtolower($p->getNom()), strtolower($data['produit_nom'])) !== false) {
                    $produit = $p;
                    break;
                }
            }
            
            // If product found, create supplier
            if ($produit) {
                $fournisseur = new Fournisseur();
                $fournisseur->setMarque($data['marque']);
                $fournisseur->setProduit($produit);
                
                $manager->persist($fournisseur);
            }
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
