<?php

namespace App\DataFixtures;

use App\Entity\TypeFenetrePorte;
use App\Entity\Systeme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Fixtures pour les types de fenêtre/porte
 */
class TypeFenetrePorteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Créer les types de fenêtre/porte de base
        $types = [
            [
                'nom' => 'Fenêtre standard',
                'systemes' => [] // Sera associé aux systèmes dans la méthode
            ],
            [
                'nom' => 'Fenêtre oscillo-battante',
                'systemes' => []
            ],
            [
                'nom' => 'Porte-fenêtre 2 vantaux',
                'systemes' => []
            ],
            [
                'nom' => 'Porte-fenêtre 1 vantail',
                'systemes' => []
            ],
            [
                'nom' => 'Fenêtre à soufflet',
                'systemes' => []
            ],
            [
                'nom' => 'Fenêtre coulissante',
                'systemes' => []
            ],
            [
                'nom' => 'Porte d\'entrée',
                'systemes' => []
            ],
            [
                'nom' => 'Baie coulissante',
                'systemes' => []
            ]
        ];

        $createdTypes = [];
        
        foreach ($types as $typeData) {
            $type = new TypeFenetrePorte();
            $type->setNom($typeData['nom']);
            
            $manager->persist($type);
            $createdTypes[] = $type;
        }

        $manager->flush();

        // Récupérer tous les systèmes existants et les associer aux types
        $systemes = $manager->getRepository(Systeme::class)->findAll();
        
        if (!empty($systemes)) {
            foreach ($createdTypes as $type) {
                // Associer chaque type à quelques systèmes (simulation d'une relation réelle)
                $systemesToAssociate = array_slice($systemes, 0, min(3, count($systemes)));
                
                foreach ($systemesToAssociate as $systeme) {
                    $systeme->addTypeFenetrePorte($type);
                }
            }
            
            $manager->flush();
        }

        // Créer des références pour d'autres fixtures si nécessaire
        foreach ($createdTypes as $index => $type) {
            $this->addReference('type-fenetre-porte-' . ($index + 1), $type);
        }
    }

    public function getDependencies(): array
    {
        return [
            // Dépend des fixtures des systèmes (si elles existent)
            // SystemeFixtures::class,
        ];
    }
}