<?php

namespace App\Service;

use TCPDF;
use App\Entity\ConfPf;
use App\Entity\ProjetPdf;
use Doctrine\ORM\EntityManagerInterface;

class PdfGeneratorService
{
    private string $projectDir;
    private EntityManagerInterface $entityManager;
    private float $calculationCoefficient;

    public function __construct(
        string $projectDir, 
        EntityManagerInterface $entityManager,
        float $calculationCoefficient = 1.5
    ) {
        $this->projectDir = $projectDir;
        $this->entityManager = $entityManager;
        $this->calculationCoefficient = $calculationCoefficient;
    }

    public function generatePlanPdf(ConfPf $confPf, float $customValue, ?float $calculatedValue = null): string
    {
        // Si la valeur calculée n'est pas fournie, la calculer avec le coefficient
        if ($calculatedValue === null) {
            $calculatedValue = $customValue * $this->calculationCoefficient;
        }
        
        // Créer une nouvelle instance TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Désactiver les en-têtes et pieds de page
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        // Définir les informations du document
        $pdf->SetCreator('Proposition Ouverture');
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Plan Technique - Projet ' . $confPf->getProjet()->getRefClient());

        // Ajouter une page
        $pdf->AddPage();

        // Utiliser directement l'image du schéma technique
        $schemaPath = $this->projectDir . '/public/assets/plans/schemaProfil.png';
        
        if (!file_exists($schemaPath)) {
            throw new \Exception("Image du schéma technique manquante : $schemaPath");
        }

        // Ajouter l'image directement dans le PDF (taille adaptée à la page A4)
        $pdf->Image($schemaPath, 15, 20, 180, 135, 'PNG');

        // Ajouter les deux valeurs sur le plan
        $this->addCustomValuesToPdf($pdf, $customValue, $calculatedValue);

        // Ajouter les informations de configuration  
        $this->addConfigurationInfo($pdf, $confPf, $customValue);

        // Générer le nom du fichier
        $fileName = 'plan_' . $confPf->getProjet()->getId() . '_' . date('YmdHis') . '.pdf';
        $filePath = $this->projectDir . '/public/uploads/pdf/' . $fileName;

        // Créer le répertoire si nécessaire
        $uploadDir = dirname($filePath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Sauvegarder le PDF
        $pdf->Output($filePath, 'F');

        // Enregistrer la relation en base de données
        $this->saveProjetPdf($confPf, $fileName, $customValue, $filePath);

        return '/uploads/pdf/' . $fileName;
    }

    private function addCustomValuesToPdf(TCPDF $pdf, float $customValue, float $calculatedValue): void
    {
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(255, 0, 0); // Rouge pour les côtes

        // Position pour la côte interne : 20mm du bord gauche, 100mm du haut
        $x1 = 20; // 20mm du bord gauche
        $y1 = 100; // 100mm du haut
        
        // Position pour la côte extérieur : 20mm du bord droit, 100mm du haut
        // Pour A4 (210mm de largeur) : 210 - 20 = 190mm du bord gauche
        $x2 = 190; // 20mm du bord droit (210mm - 20mm = 190mm)
        $y2 = 100; // même hauteur que la côte interne
        
        // Ajouter la côte interne
        $pdf->StartTransform();
        $pdf->Rotate(90, $x1, $y1);
        $pdf->SetXY($x1, $y1);
        $pdf->Cell(35, 8, number_format($customValue, 1) . ' mm', 0, 0, 'C', false);
        $pdf->StopTransform();
        
        // Ajouter la côte extérieur - côté droit
        $pdf->StartTransform();
        $pdf->Rotate(90, $x2, $y2);
        $pdf->SetXY($x2, $y2);
        $pdf->Cell(35, 8, number_format($calculatedValue, 1) . ' mm', 0, 0, 'C', false);
        $pdf->StopTransform();
    }

    /**
     * Enregistre la relation PDF/Projet en base de données
     */
    private function saveProjetPdf(ConfPf $confPf, string $fileName, float $customValue, string $fullPath): void
    {
        $projetPdf = new ProjetPdf();
        $projetPdf->setProjet($confPf->getProjet());
        $projetPdf->setConfPf($confPf);
        $projetPdf->setFileName($fileName);
        $projetPdf->setFilePath('/uploads/pdf/' . $fileName);
        $projetPdf->setCustomValue($customValue);
        $projetPdf->setFileSize(filesize($fullPath));
        
        $this->entityManager->persist($projetPdf);
        $this->entityManager->flush();
    }

    private function addConfigurationInfo(TCPDF $pdf, ConfPf $confPf, float $customValue): void
    {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0); // Noir pour les informations

        $y = 180; // Position de départ pour les informations

        // Titre
        $pdf->SetXY(10, $y);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 8, 'Informations de configuration', 0, 1, 'L');
        $y += 10;

        $pdf->SetFont('helvetica', '', 9);

        // Informations du projet
        $infos = [
            'Projet: ' . $confPf->getProjet()->getRefClient(),
            'Produit: ' . ($confPf->getProduit() ? $confPf->getProduit()->getNom() : 'Non défini'),
            'Catégorie: ' . ($confPf->getCategorie() ? $confPf->getCategorie()->getNom() : 'Non définie'),
            'Sous-catégorie: ' . ($confPf->getSousCategorie() ? $confPf->getSousCategorie()->getNom() : 'Non définie'),
            'Ouverture: ' . ($confPf->getOuverture() ? $confPf->getOuverture()->getNom() : 'Non définie'),
            'Dimensions: ' . ($confPf->getLargeur() ? $confPf->getLargeur() . ' x ' . $confPf->getHauteur() . ' mm' : 'Non définies'),
            'Quantité: ' . ($confPf->getQuantite() ?: 'Non définie'),
            'Valeur spécifique: ' . number_format($customValue, 1) . ' mm',
            'Date de génération: ' . date('d/m/Y H:i')
        ];

        foreach ($infos as $info) {
            $pdf->SetXY(10, $y);
            $pdf->Cell(0, 6, $info, 0, 1, 'L');
            $y += 6;
        }
    }
}