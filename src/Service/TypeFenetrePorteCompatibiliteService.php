<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TypeFenetrePorte;
use App\Entity\Ouverture;
use App\Entity\Systeme;
use App\Repository\TypeFenetrePorteCompatibiliteRepository;

/**
 * Service pour gérer les compatibilités entre types de fenêtre/porte, ouvertures et systèmes.
 * 
 * Centralise la logique métier de compatibilité suivant les principes SOLID.
 */
class TypeFenetrePorteCompatibiliteService
{
    public function __construct(
        private readonly TypeFenetrePorteCompatibiliteRepository $compatibiliteRepository
    ) {
    }

    /**
     * Récupère tous les types de fenêtre/porte compatibles avec une ouverture et un système.
     *
     * @param Ouverture $ouverture
     * @param Systeme $systeme
     * @return TypeFenetrePorte[]
     */
    public function getTypesCompatibles(Ouverture $ouverture, Systeme $systeme): array
    {
        return $this->compatibiliteRepository->findTypesFenetrePorteByOuvertureAndSysteme($ouverture, $systeme);
    }

    /**
     * Vérifie si un type de fenêtre/porte est compatible avec une ouverture et un système.
     *
     * @param TypeFenetrePorte $type
     * @param Ouverture $ouverture
     * @param Systeme $systeme
     * @return bool
     */
    public function isTypeCompatible(TypeFenetrePorte $type, Ouverture $ouverture, Systeme $systeme): bool
    {
        return $this->compatibiliteRepository->isCompatible(
            $type->getId(),
            $ouverture->getId(),
            $systeme->getId()
        );
    }

    /**
     * Récupère les types compatibles avec une ouverture (tous systèmes confondus).
     *
     * @param Ouverture $ouverture
     * @return array
     */
    public function getTypesByOuverture(Ouverture $ouverture): array
    {
        $compatibilites = $this->compatibiliteRepository->findByOuverture($ouverture);
        
        $types = [];
        foreach ($compatibilites as $compatibilite) {
            $type = $compatibilite->getTypeFenetrePorte();
            if ($type && !in_array($type, $types, true)) {
                $types[] = $type;
            }
        }
        
        return $types;
    }

    /**
     * Récupère les types compatibles avec un système (toutes ouvertures confondues).
     *
     * @param Systeme $systeme
     * @return array
     */
    public function getTypesBySysteme(Systeme $systeme): array
    {
        $compatibilites = $this->compatibiliteRepository->findBySysteme($systeme);
        
        $types = [];
        foreach ($compatibilites as $compatibilite) {
            $type = $compatibilite->getTypeFenetrePorte();
            if ($type && !in_array($type, $types, true)) {
                $types[] = $type;
            }
        }
        
        return $types;
    }

    /**
     * Compte le nombre de compatibilités pour un type donné.
     *
     * @param TypeFenetrePorte $type
     * @return int
     */
    public function countCompatibilites(TypeFenetrePorte $type): int
    {
        return count($type->getCompatibilites());
    }
}