<?php

namespace App\Controller;

use App\Entity\Systeme;
use App\Entity\Fournisseur;
use App\Entity\Ouverture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/temp', name: 'temp_')]
class TempSystemeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/systemes', name: 'systemes_index')]
    public function index(): Response
    {
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $fournisseurs = $this->entityManager->getRepository(Fournisseur::class)->findAll();
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();

        return $this->render('temp/systemes_index.html.twig', [
            'systemes' => $systemes,
            'fournisseurs' => $fournisseurs,
            'ouvertures' => $ouvertures,
        ]);
    }

    #[Route('/systeme/new', name: 'systeme_new', methods: ['POST'])]
    public function newSysteme(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['nom'], $data['fournisseur_id'])) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }

            $fournisseur = $this->entityManager->getRepository(Fournisseur::class)
                ->find($data['fournisseur_id']);
            
            if (!$fournisseur) {
                return new JsonResponse(['error' => 'Fournisseur non trouvé'], 404);
            }

            $systeme = new Systeme();
            $systeme->setNom($data['nom']);
            $systeme->setFournisseur($fournisseur);
            
            if (isset($data['url_image']) && !empty($data['url_image'])) {
                $systeme->setUrlImage($data['url_image']);
            }

            // Associer les ouvertures sélectionnées
            if (isset($data['ouvertures']) && is_array($data['ouvertures'])) {
                foreach ($data['ouvertures'] as $ouvertureId) {
                    $ouverture = $this->entityManager->getRepository(Ouverture::class)
                        ->find($ouvertureId);
                    if ($ouverture) {
                        $systeme->addOuverture($ouverture);
                    }
                }
            }

            $this->entityManager->persist($systeme);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'systeme' => [
                    'id' => $systeme->getId(),
                    'nom' => $systeme->getNom(),
                    'fournisseur' => $systeme->getFournisseur()->getMarque(),
                    'url_image' => $systeme->getUrlImage(),
                    'ouvertures_count' => $systeme->getOuvertures()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/systeme/{id}/edit', name: 'systeme_edit', methods: ['PUT'])]
    public function editSysteme(Systeme $systeme, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (isset($data['nom'])) {
                $systeme->setNom($data['nom']);
            }
            
            if (isset($data['url_image'])) {
                $systeme->setUrlImage($data['url_image']);
            }

            if (isset($data['fournisseur_id'])) {
                $fournisseur = $this->entityManager->getRepository(Fournisseur::class)
                    ->find($data['fournisseur_id']);
                if ($fournisseur) {
                    $systeme->setFournisseur($fournisseur);
                }
            }

            // Mettre à jour les ouvertures
            if (isset($data['ouvertures'])) {
                // Supprimer toutes les associations actuelles
                foreach ($systeme->getOuvertures() as $ouverture) {
                    $systeme->removeOuverture($ouverture);
                }
                
                // Ajouter les nouvelles associations
                foreach ($data['ouvertures'] as $ouvertureId) {
                    $ouverture = $this->entityManager->getRepository(Ouverture::class)
                        ->find($ouvertureId);
                    if ($ouverture) {
                        $systeme->addOuverture($ouverture);
                    }
                }
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'systeme' => [
                    'id' => $systeme->getId(),
                    'nom' => $systeme->getNom(),
                    'fournisseur' => $systeme->getFournisseur()->getMarque(),
                    'url_image' => $systeme->getUrlImage(),
                    'ouvertures_count' => $systeme->getOuvertures()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/systeme/{id}/delete', name: 'systeme_delete', methods: ['DELETE'])]
    public function deleteSysteme(Systeme $systeme): JsonResponse
    {
        try {
            $this->entityManager->remove($systeme);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/systeme/{id}/ouvertures', name: 'systeme_ouvertures', methods: ['GET'])]
    public function getSystemeOuvertures(Systeme $systeme): JsonResponse
    {
        $ouvertures = [];
        foreach ($systeme->getOuvertures() as $ouverture) {
            $ouvertures[] = [
                'id' => $ouverture->getId(),
                'nom' => $ouverture->getNom()
            ];
        }

        return new JsonResponse($ouvertures);
    }

    #[Route('/systeme/{id}/toggle-ouverture/{ouvertureId}', name: 'systeme_toggle_ouverture', methods: ['POST'])]
    public function toggleOuverture(Systeme $systeme, int $ouvertureId): JsonResponse
    {
        try {
            $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
            
            if (!$ouverture) {
                return new JsonResponse(['error' => 'Ouverture non trouvée'], 404);
            }

            if ($systeme->getOuvertures()->contains($ouverture)) {
                $systeme->removeOuverture($ouverture);
                $action = 'removed';
            } else {
                $systeme->addOuverture($ouverture);
                $action = 'added';
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'action' => $action,
                'ouvertures_count' => $systeme->getOuvertures()->count()
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/bulk-associate', name: 'bulk_associate', methods: ['POST'])]
    public function bulkAssociate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['systeme_ids'], $data['ouverture_ids'])) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }

            $associationsCount = 0;
            
            foreach ($data['systeme_ids'] as $systemeId) {
                $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
                if (!$systeme) continue;

                foreach ($data['ouverture_ids'] as $ouvertureId) {
                    $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
                    if (!$ouverture) continue;

                    if (!$systeme->getOuvertures()->contains($ouverture)) {
                        $systeme->addOuverture($ouverture);
                        $associationsCount++;
                    }
                }
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'associations_created' => $associationsCount
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}