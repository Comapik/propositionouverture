<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\TypeFenetrePorte;
use App\Entity\Systeme;
use App\Entity\Ouverture;
use App\Entity\TypeFenetrePorteCompatibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur temporaire pour gérer les types de fenêtre/porte
 * TODO: À supprimer en production
 */
#[Route('/temp/type-fenetre-porte')]
class TempTypeFenetrePorteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_temp_type_fenetre_porte_index', methods: ['GET'])]
    public function index(): Response
    {
        $types = $this->entityManager->getRepository(TypeFenetrePorte::class)->findAll();
        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findAll();

        // Récupérer les compatibilités pour chaque type
        $compatibilites = [];
        foreach ($types as $type) {
            $compatibilites[$type->getId()] = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
                ->findBy(['typeFenetrePorte' => $type]);
        }

        return $this->render('temp/type_fenetre_porte/index.html.twig', [
            'types' => $types,
            'systemes' => $systemes,
            'ouvertures' => $ouvertures,
            'compatibilites' => $compatibilites,
        ]);
    }

    #[Route('/create', name: 'app_temp_type_fenetre_porte_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $nom = $request->request->get('nom');
        $ouvertureIds = $request->request->all('ouvertures') ?? [];
        $systemeIds = $request->request->all('systemes') ?? [];

        if (empty($nom)) {
            $this->addFlash('error', 'Le nom est obligatoire');
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        // Créer le nouveau type
        $type = new TypeFenetrePorte();
        $type->setNom($nom);
        $this->entityManager->persist($type);
        $this->entityManager->flush(); // Nécessaire pour obtenir l'ID du type

        // Créer les compatibilités pour chaque combinaison ouverture/système
        $compatibilityCount = 0;
        foreach ($ouvertureIds as $ouvertureId) {
            $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
            if (!$ouverture) continue;

            foreach ($systemeIds as $systemeId) {
                $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
                if (!$systeme) continue;

                // Vérifier si la compatibilité existe déjà
                $existingCompatibility = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
                    ->findOneBy([
                        'typeFenetrePorte' => $type,
                        'ouverture' => $ouverture,
                        'systeme' => $systeme
                    ]);

                if (!$existingCompatibility) {
                    $compatibility = new TypeFenetrePorteCompatibilite();
                    $compatibility->setTypeFenetrePorte($type);
                    $compatibility->setOuverture($ouverture);
                    $compatibility->setSysteme($systeme);
                    $this->entityManager->persist($compatibility);
                    $compatibilityCount++;
                }
            }
        }

        if ($compatibilityCount > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', "Type de fenêtre/porte '{$nom}' créé avec {$compatibilityCount} compatibilités");
        } else {
            $this->entityManager->flush();
            $this->addFlash('warning', "Type de fenêtre/porte '{$nom}' créé mais aucune compatibilité définie");
        }

        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }

    #[Route('/{id}/associate', name: 'app_temp_type_fenetre_porte_associate', methods: ['POST'])]
    public function associate(TypeFenetrePorte $type, Request $request): Response
    {
        $systemeIds = $request->request->all('systemes') ?? [];
        $ouvertureIds = $request->request->all('ouvertures') ?? [];

        // Supprimer toutes les compatibilités existantes pour ce type
        $existingCompatibilities = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findBy(['typeFenetrePorte' => $type]);
        foreach ($existingCompatibilities as $compatibility) {
            $this->entityManager->remove($compatibility);
        }
        $this->entityManager->flush();

        // Créer les nouvelles compatibilités
        $compatibilityCount = 0;
        foreach ($ouvertureIds as $ouvertureId) {
            $ouverture = $this->entityManager->getRepository(Ouverture::class)->find($ouvertureId);
            if (!$ouverture) continue;

            foreach ($systemeIds as $systemeId) {
                $systeme = $this->entityManager->getRepository(Systeme::class)->find($systemeId);
                if (!$systeme) continue;

                $compatibility = new TypeFenetrePorteCompatibilite();
                $compatibility->setTypeFenetrePorte($type);
                $compatibility->setOuverture($ouverture);
                $compatibility->setSysteme($systeme);
                $this->entityManager->persist($compatibility);
                $compatibilityCount++;
            }
        }

        if ($compatibilityCount > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', "Relations mises à jour : {$compatibilityCount} compatibilités créées");
        } else {
            $this->addFlash('warning', 'Aucune compatibilité créée. Vérifiez vos sélections.');
        }
        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }

    #[Route('/{id}/delete', name: 'app_temp_type_fenetre_porte_delete', methods: ['POST'])]
    public function delete(TypeFenetrePorte $type): Response
    {
        // Supprimer toutes les compatibilités associées
        $existingCompatibilities = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findBy(['typeFenetrePorte' => $type]);
        foreach ($existingCompatibilities as $compatibility) {
            $this->entityManager->remove($compatibility);
        }

        // Supprimer le type lui-même
        $this->entityManager->remove($type);
        $this->entityManager->flush();

        $this->addFlash('success', 'Type de fenêtre/porte supprimé avec succès');
        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }

    #[Route('/bulk-create', name: 'app_temp_type_fenetre_porte_bulk_create', methods: ['POST'])]
    public function bulkCreate(): Response
    {
        $defaultTypes = [
            'Fenêtre standard',
            'Fenêtre oscillo-battante',
            'Porte-fenêtre 2 vantaux',
            'Porte-fenêtre 1 vantail',
            'Fenêtre à soufflet',
            'Fenêtre coulissante',
            'Porte d\'entrée',
            'Baie coulissante',
            'Fenêtre fixe',
            'Porte de service',
            'Fenêtre à guillotine',
            'Fenêtre basculante'
        ];

        $systemes = $this->entityManager->getRepository(Systeme::class)->findAll();
        $created = 0;

        foreach ($defaultTypes as $typeName) {
            // Vérifier si le type existe déjà
            $existingType = $this->entityManager->getRepository(TypeFenetrePorte::class)
                ->findOneBy(['nom' => $typeName]);

            if (!$existingType) {
                $type = new TypeFenetrePorte();
                $type->setNom($typeName);
                $this->entityManager->persist($type);
                $this->entityManager->flush(); // Nécessaire pour obtenir l'ID
                
                // Obtenir les premières ouvertures et systèmes disponibles
                $ouvertures = $this->entityManager->getRepository(Ouverture::class)->findBy([], null, 2);
                $systemesToAssociate = array_slice($systemes, 0, min(3, count($systemes)));
                
                $compatibilityCount = 0;
                foreach ($ouvertures as $ouverture) {
                    foreach ($systemesToAssociate as $systeme) {
                        $compatibility = new TypeFenetrePorteCompatibilite();
                        $compatibility->setTypeFenetrePorte($type);
                        $compatibility->setOuverture($ouverture);
                        $compatibility->setSysteme($systeme);
                        $this->entityManager->persist($compatibility);
                        $compatibilityCount++;
                    }
                }
                
                if ($compatibilityCount > 0) {
                    $created++;
                }
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', "$created types de fenêtre/porte créés avec succès");
        } else {
            $this->addFlash('info', 'Tous les types par défaut existent déjà');
        }

        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }

    #[Route('/{id}/add-ouverture', name: 'app_temp_type_fenetre_porte_add_ouverture', methods: ['POST'])]
    public function addOuvertureCompatible(TypeFenetrePorte $type, Request $request): Response
    {
        $ouvertureIds = $request->request->all('ouverture_ids');
        if (!$ouvertureIds || count($ouvertureIds) === 0) {
            $single = $request->request->get('ouverture_id');
            if ($single) {
                $ouvertureIds = [$single];
            }
        }
        if (!$ouvertureIds || count($ouvertureIds) === 0) {
            $this->addFlash('error', "Aucune ouverture sélectionnée");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        $ouvertures = [];
        foreach ($ouvertureIds as $oid) {
            $ouv = $this->entityManager->getRepository(Ouverture::class)->find($oid);
            if ($ouv) {
                $ouvertures[$ouv->getId()] = $ouv;
            }
        }
        if (count($ouvertures) === 0) {
            $this->addFlash('error', "Aucune ouverture valide trouvée");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        // Récupérer les systèmes déjà compatibles avec ce type
        $existingCompatibilities = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findBy(['typeFenetrePorte' => $type]);

        $systemesUniques = [];
        foreach ($existingCompatibilities as $compat) {
            $systeme = $compat->getSysteme();
            if ($systeme) {
                $systemesUniques[$systeme->getId()] = $systeme;
            }
        }

        if (count($systemesUniques) === 0) {
            $this->addFlash('warning', "Aucun système déjà compatible pour ce type. Définissez d'abord des systèmes via 'Relations'.");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        $created = 0;
        $skipped = 0;
        foreach ($ouvertures as $ouverture) {
            foreach ($systemesUniques as $systeme) {
                // Vérifier si la compatibilité existe déjà
                $existing = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)->findOneBy([
                    'typeFenetrePorte' => $type,
                    'ouverture' => $ouverture,
                    'systeme' => $systeme,
                ]);

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $compat = new TypeFenetrePorteCompatibilite();
                $compat->setTypeFenetrePorte($type);
                $compat->setOuverture($ouverture);
                $compat->setSysteme($systeme);
                $this->entityManager->persist($compat);
                $created++;
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', sprintf(
                "Ajouté %d compatibilité(s) pour %d ouverture(s) (doublons ignorés: %d)",
                $created,
                count($ouvertures),
                $skipped
            ));
        } else {
            $this->addFlash('info', "Aucune compatibilité créée (tout existait déjà).");
        }

        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }

    #[Route('/{id}/add-systeme', name: 'app_temp_type_fenetre_porte_add_systeme', methods: ['POST'])]
    public function addSystemeCompatible(TypeFenetrePorte $type, Request $request): Response
    {
        $systemeIds = $request->request->all('systeme_ids');
        if (!$systemeIds || count($systemeIds) === 0) {
            $single = $request->request->get('systeme_id');
            if ($single) {
                $systemeIds = [$single];
            }
        }
        if (!$systemeIds || count($systemeIds) === 0) {
            $this->addFlash('error', "Aucun système sélectionné");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        $systemes = [];
        foreach ($systemeIds as $sid) {
            $sys = $this->entityManager->getRepository(Systeme::class)->find($sid);
            if ($sys) {
                $systemes[$sys->getId()] = $sys;
            }
        }
        if (count($systemes) === 0) {
            $this->addFlash('error', "Aucun système valide trouvé");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        // Récupérer les ouvertures déjà compatibles avec ce type
        $existingCompatibilities = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)
            ->findBy(['typeFenetrePorte' => $type]);

        $ouverturesUniques = [];
        foreach ($existingCompatibilities as $compat) {
            $ouverture = $compat->getOuverture();
            if ($ouverture) {
                $ouverturesUniques[$ouverture->getId()] = $ouverture;
            }
        }

        if (count($ouverturesUniques) === 0) {
            $this->addFlash('warning', "Aucune ouverture déjà compatible pour ce type. Définissez d'abord des ouvertures via 'Relations'.");
            return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
        }

        $created = 0;
        $skipped = 0;
        foreach ($systemes as $systeme) {
            foreach ($ouverturesUniques as $ouverture) {
                // Vérifier si la compatibilité existe déjà
                $existing = $this->entityManager->getRepository(TypeFenetrePorteCompatibilite::class)->findOneBy([
                    'typeFenetrePorte' => $type,
                    'ouverture' => $ouverture,
                    'systeme' => $systeme,
                ]);

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $compat = new TypeFenetrePorteCompatibilite();
                $compat->setTypeFenetrePorte($type);
                $compat->setOuverture($ouverture);
                $compat->setSysteme($systeme);
                $this->entityManager->persist($compat);
                $created++;
            }
        }

        if ($created > 0) {
            $this->entityManager->flush();
            $this->addFlash('success', sprintf(
                "Ajouté %d compatibilité(s) pour %d système(s) (doublons ignorés: %d)",
                $created,
                count($systemes),
                $skipped
            ));
        } else {
            $this->addFlash('info', "Aucune compatibilité créée (tout existait déjà).");
        }

        return $this->redirectToRoute('app_temp_type_fenetre_porte_index');
    }
}