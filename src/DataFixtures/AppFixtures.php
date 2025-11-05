<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Projet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures for Client and Projet entities.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Creates test data only
 * - Dependency Inversion: Depends on ObjectManager abstraction
 * 
 * Following DRY principle: Centralized test data creation
 * Following KISS principle: Simple data creation
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des clients
        $clients = [
            [
                'nom' => 'Jean Dupont',
                'email' => 'jean.dupont@email.com',
                'tel' => '0123456789'
            ],
            [
                'nom' => 'Marie Martin',
                'email' => 'marie.martin@email.com',
                'tel' => '0987654321'
            ],
            [
                'nom' => 'Pierre Bernard',
                'email' => 'pierre.bernard@email.com',
                'tel' => '0567891234'
            ],
            [
                'nom' => 'Sophie Dubois',
                'email' => 'sophie.dubois@email.com',
                'tel' => '0345678912'
            ],
            [
                'nom' => 'Michel Rousseau',
                'email' => 'michel.rousseau@email.com',
                'tel' => '0678912345'
            ]
        ];

        $clientEntities = [];
        foreach ($clients as $clientData) {
            $client = new Client();
            $client->setNom($clientData['nom'])
                   ->setEmail($clientData['email'])
                   ->setTel($clientData['tel']);
            
            $manager->persist($client);
            $clientEntities[] = $client;
        }

        // Création des projets
        $projets = [
            [
                'refClient' => 'REF-2024-001',
                'lieu' => '123 Rue de la Paix, Paris',
                'description' => 'Installation de fenêtres PVC double vitrage pour un appartement de 3 pièces. Remplacement de 5 fenêtres existantes avec volets roulants électriques.',
                'client' => $clientEntities[0]
            ],
            [
                'refClient' => 'REF-2024-002',
                'lieu' => '45 Avenue des Champs, Lyon',
                'description' => 'Pose de portes-fenêtres en aluminium avec volets battants pour une maison individuelle. Projet comprenant également l\'installation d\'une baie vitrée coulissante.',
                'client' => $clientEntities[1]
            ],
            [
                'refClient' => 'REF-2024-003',
                'lieu' => '78 Boulevard Victor Hugo, Marseille',
                'description' => 'Rénovation complète des menuiseries extérieures d\'une villa. Installation de fenêtres bois-aluminium avec triple vitrage et volets roulants solaires.',
                'client' => $clientEntities[2]
            ],
            [
                'refClient' => 'REF-2024-004',
                'lieu' => '12 Place de la République, Toulouse',
                'description' => 'Installation de fenêtres de toit VELUX avec volets roulants intégrés pour l\'aménagement de combles.',
                'client' => $clientEntities[3]
            ],
            [
                'refClient' => 'REF-2024-005',
                'lieu' => '67 Rue Saint-Antoine, Bordeaux',
                'description' => 'Pose de volets roulants électriques sur fenêtres existantes. Motorisation avec télécommande et programmation automatique.',
                'client' => $clientEntities[4]
            ],
            [
                'refClient' => 'REF-2024-006',
                'lieu' => '34 Cours Mirabeau, Aix-en-Provence',
                'description' => 'Installation d\'une véranda en aluminium avec toiture en verre et volets roulants périphériques.',
                'client' => $clientEntities[0]
            ],
            [
                'refClient' => 'REF-2024-007',
                'lieu' => '89 Rue des Roses, Nice',
                'description' => 'Remplacement de fenêtres anciennes par des fenêtres PVC avec volets battants en aluminium thermolaqué.',
                'client' => $clientEntities[2]
            ]
        ];

        foreach ($projets as $index => $projetData) {
            $projet = new Projet();
            $projet->setRefClient($projetData['refClient'])
                   ->setLieu($projetData['lieu'])
                   ->setDescription($projetData['description'])
                   ->setClient($projetData['client'])
                   ->setCreatedAt(new \DateTimeImmutable('-' . ($index * 5) . ' days'));
            
            $manager->persist($projet);
        }

        $manager->flush();
    }
}
