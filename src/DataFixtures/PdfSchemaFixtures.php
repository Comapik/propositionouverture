<?php

namespace App\DataFixtures;

use App\Entity\PdfSchema;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PdfSchemaFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer 5 schémas PDF différents
        $schemas = [
            [
                'nom' => 'Schéma Standard',
                'description' => 'Plan technique standard avec dimensions de base',
                'imagePath' => '/assets/plans/schemaProfil.png',
                'previewImage' => '/assets/plans/previews/schema_standard.png',
                'ordre' => 1
            ],
            [
                'nom' => 'Schéma Détaillé',
                'description' => 'Plan avec détails techniques avancés',
                'imagePath' => '/assets/plans/schema_detaille.png',
                'previewImage' => '/assets/plans/previews/schema_detaille.png',
                'ordre' => 2
            ],
            [
                'nom' => 'Schéma Simplifié',
                'description' => 'Version allégée pour présentation client',
                'imagePath' => '/assets/plans/schema_simplifie.png',
                'previewImage' => '/assets/plans/previews/schema_simplifie.png',
                'ordre' => 3
            ],
            [
                'nom' => 'Schéma Technique',
                'description' => 'Plan technique complet avec spécifications',
                'imagePath' => '/assets/plans/schema_technique.png',
                'previewImage' => '/assets/plans/previews/schema_technique.png',
                'ordre' => 4
            ],
            [
                'nom' => 'Schéma Commercial',
                'description' => 'Format optimisé pour devis commercial',
                'imagePath' => '/assets/plans/schema_commercial.png',
                'previewImage' => '/assets/plans/previews/schema_commercial.png',
                'ordre' => 5
            ]
        ];

        foreach ($schemas as $schemaData) {
            $schema = new PdfSchema();
            $schema->setNom($schemaData['nom']);
            $schema->setDescription($schemaData['description']);
            $schema->setImagePath($schemaData['imagePath']);
            $schema->setPreviewImage($schemaData['previewImage']);
            $schema->setOrdre($schemaData['ordre']);
            $schema->setActif(true);
            
            $manager->persist($schema);
        }

        $manager->flush();
    }
}