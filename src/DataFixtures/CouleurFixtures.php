<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Couleur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * CouleurFixtures following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Loads test couleur data
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized test data management
 * Following KISS principle: Simple fixture structure
 */
class CouleurFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Couleurs avec code hexadécimal (plaxage_laquage_id = 1)
        $couleursHex = [
            ['nom' => 'Blanc', 'codeHex' => '#FFFFFF', 'description' => 'Blanc classique'],
            ['nom' => 'Noir', 'codeHex' => '#000000', 'description' => 'Noir profond'],
            ['nom' => 'Gris anthracite', 'codeHex' => '#2F2F2F', 'description' => 'Gris foncé moderne'],
            ['nom' => 'Bleu marine', 'codeHex' => '#003366', 'description' => 'Bleu marine élégant'],
            ['nom' => 'Vert sapin', 'codeHex' => '#0F5132', 'description' => 'Vert foncé naturel'],
            ['nom' => 'Rouge bordeaux', 'codeHex' => '#722F37', 'description' => 'Rouge profond'],
            ['nom' => 'Beige', 'codeHex' => '#F5F5DC', 'description' => 'Beige chaleureux'],
        ];

        foreach ($couleursHex as $index => $couleurData) {
            $couleur = new Couleur();
            $couleur->setNom($couleurData['nom']);
            $couleur->setCodeHex($couleurData['codeHex']);
            $couleur->setPlaxageLaquageId(1); // Code hex
            $couleur->setDescription($couleurData['description']);
            $couleur->setActif(true);
            
            $manager->persist($couleur);
            $this->addReference('couleur-hex-' . $index, $couleur);
        }

        // Couleurs avec images (plaxage_laquage_id != 1)
        $couleursImage = [
            ['nom' => 'Bois chêne naturel', 'urlImage' => 'https://example.com/images/chene-naturel.jpg', 'description' => 'Texture bois chêne naturel'],
            ['nom' => 'Bois pin', 'urlImage' => 'https://example.com/images/pin.jpg', 'description' => 'Texture bois de pin'],
            ['nom' => 'Métal brossé', 'urlImage' => 'https://example.com/images/metal-brosse.jpg', 'description' => 'Finition métal brossé'],
            ['nom' => 'Pierre naturelle', 'urlImage' => 'https://example.com/images/pierre-naturelle.jpg', 'description' => 'Texture pierre naturelle'],
            ['nom' => 'Aluminium anodisé', 'urlImage' => 'https://example.com/images/alu-anodise.jpg', 'description' => 'Finition aluminium anodisé'],
        ];

        foreach ($couleursImage as $index => $couleurData) {
            $couleur = new Couleur();
            $couleur->setNom($couleurData['nom']);
            $couleur->setUrlImage($couleurData['urlImage']);
            $couleur->setPlaxageLaquageId(2); // Image/texture
            $couleur->setDescription($couleurData['description']);
            $couleur->setActif(true);
            
            $manager->persist($couleur);
            $this->addReference('couleur-image-' . $index, $couleur);
        }

        $manager->flush();
    }
}