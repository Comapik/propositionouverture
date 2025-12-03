<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TypeFenetrePorte;
use App\Entity\TypeFenetrePorteCompatibilite;
use App\Entity\Systeme;
use App\Entity\Ouverture;
use App\Service\TypeFenetrePorteCompatibiliteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour gérer les compatibilités entre types de fenêtre/porte, ouvertures et systèmes.
 */
#[Route('/admin/compatibilites')]
class CompatibiliteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeFenetrePorteCompatibiliteService $compatibiliteService
    ) {
    }

    #[Route('/', name: 'app_admin_compatibilites_index', methods: ['GET'])]
    public function index(): Response
    {
        $types = $this->entityManager->getRepository(TypeFenetrePorte::class)->findBy([], ['nom' => 'ASC']);
        $systemes = $this->entityManager->getRepository(Systeme::class)->findBy([], ['nom' => 'ASC']);
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findBy([], ['nom' => 'ASC']);

        return $this->render('admin/compatibilites/index.html.twig', [
            'types' => $types,
            'systemes' => $systemes,
            'ouvertures' => $ouvertures,
        ]);
    }

    #[Route('/type/{id}', name: 'app_admin_compatibilites_type', methods: ['GET'])]
    public function showType(TypeFenetrePorte $type): Response
    {
        $compatibilites = $this->entityManager
            ->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findBy(['typeFenetrePorte' => $type], ['ouverture' => 'ASC']);

        $systemes = $this->entityManager->getRepository(Systeme::class)->findBy([], ['nom' => 'ASC']);
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findBy([], ['nom' => 'ASC']);

        // Organiser les compatibilités par ouverture
        $compatibilitesByOuverture = [];
        foreach ($compatibilites as $compatibilite) {
            $ouvertureNom = $compatibilite->getOuverture()->getNom();
            if (!isset($compatibilitesByOuverture[$ouvertureNom])) {
                $compatibilitesByOuverture[$ouvertureNom] = [];
            }
            $compatibilitesByOuverture[$ouvertureNom][] = $compatibilite;
        }

        return $this->render('admin/compatibilites/type.html.twig', [
            'type' => $type,
            'compatibilites' => $compatibilites,
            'compatibilitesByOuverture' => $compatibilitesByOuverture,
            'systemes' => $systemes,
            'ouvertures' => $ouvertures,
        ]);
    }

    #[Route('/add', name: 'app_admin_compatibilites_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $typeId = $request->request->get('type_id');
        $ouvertureId = $request->request->get('ouverture_id');
        $systemeId = $request->request->get('systeme_id');

        if (!$typeId || !$ouvertureId || !$systemeId) {
            $this->addFlash('error', 'Tous les champs sont obligatoires');
            return $this->redirectToRoute('app_admin_compatibilites_index');
        }

        $type = $this->entityManager->getRepository(TypeFenetrePorte::class)->find($typeId);
        $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
        $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);

        if (!$type || !$ouverture || !$systeme) {
            $this->addFlash('error', 'Entité introuvable');
            return $this->redirectToRoute('app_admin_compatibilites_index');
        }

        // Vérifier si la compatibilité existe déjà
        $existing = $this->entityManager
            ->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findOneBy([
                'typeFenetrePorte' => $type,
                'ouverture' => $ouverture,
                'systeme' => $systeme,
            ]);

        if ($existing) {
            $this->addFlash('warning', 'Cette compatibilité existe déjà');
        } else {
            $compatibilite = new TypeFenetrePorteCompatibilite();
            $compatibilite->setTypeFenetrePorte($type);
            $compatibilite->setOuverture($ouverture);
            $compatibilite->setSysteme($systeme);

            $this->entityManager->persist($compatibilite);
            $this->entityManager->flush();

            $this->addFlash('success', 'Compatibilité ajoutée avec succès');
        }

        return $this->redirectToRoute('app_admin_compatibilites_type', ['id' => $type->getId()]);
    }

    #[Route('/remove/{id}', name: 'app_admin_compatibilites_remove', methods: ['POST'])]
    public function remove(TypeFenetrePorteCompatibilite $compatibilite): Response
    {
        $typeId = $compatibilite->getTypeFenetrePorte()->getId();
        
        $this->entityManager->remove($compatibilite);
        $this->entityManager->flush();

        $this->addFlash('success', 'Compatibilité supprimée avec succès');
        return $this->redirectToRoute('app_admin_compatibilites_type', ['id' => $typeId]);
    }

    #[Route('/bulk-add', name: 'app_admin_compatibilites_bulk_add', methods: ['POST'])]
    public function bulkAdd(Request $request): Response
    {
        $typeId = $request->request->get('type_id');
        $ouvertureIds = $request->request->all('ouverture_ids') ?? [];
        $systemeIds = $request->request->all('systeme_ids') ?? [];

        if (!$typeId || empty($ouvertureIds) || empty($systemeIds)) {
            $this->addFlash('error', 'Veuillez sélectionner un type, au moins une ouverture et un système');
            return $this->redirectToRoute('app_admin_compatibilites_index');
        }

        $type = $this->entityManager->getRepository(TypeFenetrePorte::class)->find($typeId);
        if (!$type) {
            $this->addFlash('error', 'Type introuvable');
            return $this->redirectToRoute('app_admin_compatibilites_index');
        }

        $added = 0;
        $skipped = 0;

        foreach ($ouvertureIds as $ouvertureId) {
            $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
            if (!$ouverture) continue;

            foreach ($systemeIds as $systemeId) {
                $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
                if (!$systeme) continue;

                // Vérifier si existe déjà
                $existing = $this->entityManager
                    ->getRepository(TypeFenetrePorteCompatibilite::class)
                    ->findOneBy([
                        'typeFenetrePorte' => $type,
                        'ouverture' => $ouverture,
                        'systeme' => $systeme,
                    ]);

                if (!$existing) {
                    $compatibilite = new TypeFenetrePorteCompatibilite();
                    $compatibilite->setTypeFenetrePorte($type);
                    $compatibilite->setOuverture($ouverture);
                    $compatibilite->setSysteme($systeme);

                    $this->entityManager->persist($compatibilite);
                    $added++;
                } else {
                    $skipped++;
                }
            }
        }

        $this->entityManager->flush();

        $this->addFlash('success', "$added compatibilités ajoutées ($skipped déjà existantes)");
        return $this->redirectToRoute('app_admin_compatibilites_type', ['id' => $type->getId()]);
    }

    #[Route('/api/check', name: 'app_admin_compatibilites_api_check', methods: ['GET'])]
    public function apiCheck(Request $request): JsonResponse
    {
        $typeId = $request->query->get('type');
        $ouvertureId = $request->query->get('ouverture');
        $systemeId = $request->query->get('systeme');

        if (!$typeId || !$ouvertureId || !$systemeId) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        $isCompatible = $this->compatibiliteService->isTypeCompatible(
            $this->entityManager->getRepository(TypeFenetrePorte::class)->find($typeId),
            $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId),
            $this->entityManager->getRepository(Systeme::class)->find($systemeId)
        );

        return new JsonResponse(['compatible' => $isCompatible]);
    }

    #[Route('/api/types-by-ouverture-systeme', name: 'app_admin_compatibilites_api_types', methods: ['GET'])]
    public function apiTypesByOuvertureSysteme(Request $request): JsonResponse
    {
        $ouvertureId = $request->query->get('ouverture');
        $systemeId = $request->query->get('systeme');

        if (!$ouvertureId || !$systemeId) {
            return new JsonResponse(['error' => 'Paramètres manquants'], 400);
        }

        $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
        $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);

        if (!$ouverture || !$systeme) {
            return new JsonResponse(['error' => 'Entité introuvable'], 404);
        }

        $types = $this->compatibiliteService->getTypesCompatibles($ouverture, $systeme);

        $data = array_map(fn($type) => [
            'id' => $type->getId(),
            'nom' => $type->getNom()
        ], $types);

        return new JsonResponse($data);
    }
}