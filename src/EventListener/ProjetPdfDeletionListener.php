<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\ProjetPdf;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsDoctrineListener(event: Events::preRemove)]
class ProjetPdfDeletionListener
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifier que c'est bien une entité ProjetPdf
        if (!($entity instanceof ProjetPdf)) {
            return;
        }

        // Récupérer le chemin du fichier
        $filePath = $entity->getFilePath();
        
        if ($filePath === null || $filePath === '') {
            return;
        }

        // Construire le chemin complet du fichier
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $fullPath = $projectDir . '/public' . $filePath;

        // Supprimer le fichier s'il existe
        if (file_exists($fullPath)) {
            try {
                unlink($fullPath);
            } catch (\Exception $e) {
                // Enregistrer l'erreur mais ne pas bloquer la suppression de l'entité
                error_log('Erreur lors de la suppression du fichier PDF: ' . $fullPath . ' - ' . $e->getMessage());
            }
        }
    }
}
