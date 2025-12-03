<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Couleur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforme les valeurs de couleur (ID ou valeurs spéciales) en entités Couleur
 */
class CouleurTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Transforme une entité Couleur en valeur pour le formulaire
     */
    public function transform($value): string
    {
        if (!$value instanceof Couleur) {
            return '';
        }

        return (string) $value->getId();
    }

    /**
     * Transforme une valeur du formulaire en entité Couleur
     */
    public function reverseTransform($value): ?Couleur
    {
        if (null === $value || '' === $value) {
            return null;
        }

        // Convertir en string pour éviter les erreurs de type avec str_starts_with
        $stringValue = (string) $value;

        // Gérer les couleurs spéciales
        if (str_starts_with($stringValue, 'special_')) {
            return $this->handleSpecialColor($stringValue);
        }

        // Gérer les couleurs normales (ID)
        if (is_numeric($value)) {
            $couleur = $this->entityManager->getRepository(Couleur::class)->find($value);
            if (!$couleur) {
                throw new TransformationFailedException(sprintf('Couleur avec l\'ID "%s" non trouvée.', $value));
            }
            return $couleur;
        }

        throw new TransformationFailedException(sprintf('Valeur de couleur invalide: "%s"', $value));
    }

    private function handleSpecialColor(string $value): Couleur
    {
        $couleurRepo = $this->entityManager->getRepository(Couleur::class);

        if ($value === 'special_blanc') {
            return $this->getOrCreateSpecialColor('Blanc', '#FFFFFF', $couleurRepo);
        }

        if ($value === 'special_creme') {
            return $this->getOrCreateSpecialColor('Crème', '#F5F5DC', $couleurRepo);
        }

        throw new TransformationFailedException(sprintf('Couleur spéciale inconnue: "%s"', $value));
    }

    private function getOrCreateSpecialColor(string $nom, string $codeHex, $couleurRepo): Couleur
    {
        // Chercher si la couleur existe déjà
        $couleur = $couleurRepo->findOneBy(['nom' => $nom, 'codeHex' => $codeHex]);

        if (!$couleur) {
            // Créer la nouvelle couleur
            $couleur = new Couleur();
            $couleur->setNom($nom);
            $couleur->setCodeHex($codeHex);
            $couleur->setPlaxageLaquageId(99); // ID spécial pour les couleurs fixes

            $this->entityManager->persist($couleur);
            $this->entityManager->flush();
        }

        return $couleur;
    }
}