<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Couleur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de test pour l'aperçu des couleurs
 */
class CouleurTestController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/test/couleurs', name: 'app_test_couleurs', methods: ['GET'])]
    public function testCouleurs(): Response
    {
        // Récupérer quelques couleurs pour tester l'affichage
        $couleursRAL = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.plaxageLaquageId = 1')
            ->andWhere('c.codeHex IS NOT NULL')
            ->orderBy('c.nom', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        $couleursRenolit = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->where('c.plaxageLaquageId = 2')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('test/couleurs.html.twig', [
            'couleursRAL' => $couleursRAL,
            'couleursRenolit' => $couleursRenolit,
        ]);
    }
}