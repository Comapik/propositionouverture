<?php

namespace App\Service;

use TCPDF;
use App\Entity\ConfPf;
use App\Entity\ProjetPdf;
use App\Entity\PdfSchema;
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

    public function generatePlanPdf(ConfPf $confPf, float $customValue, ?float $calculatedValue = null, ?PdfSchema $pdfSchema = null, ?array $additionalValues = null): string
    {
        // Si la valeur calculée n'est pas fournie, la calculer avec le coefficient
        if ($calculatedValue === null) {
            $calculatedValue = $customValue * $this->calculationCoefficient;
        }
        
        // Si aucun schéma n'est fourni, utiliser le schéma par défaut
        if ($pdfSchema === null) {
            $pdfSchema = $this->entityManager->getRepository(PdfSchema::class)->findDefaultSchema();
            if (!$pdfSchema) {
                throw new \Exception('Aucun schéma PDF par défaut trouvé.');
            }
        }
        
        // Créer une nouvelle instance TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Désactiver les en-têtes et pieds de page
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        // Supprimer les marges et les sauts automatiques pour éviter une page vide
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        // Définir les informations du document
        $pdf->SetCreator('Proposition Ouverture');
        $pdf->SetAuthor('System');
        $pdf->SetTitle('Plan Technique - Projet ' . $confPf->getProjet()->getRefClient());

        // Ajouter une page
        $pdf->AddPage();

        // Utiliser l'image du schéma sélectionné
        $schemaPath = $this->projectDir . '/public' . $pdfSchema->getImagePath();
        
        if (!file_exists($schemaPath)) {
            throw new \Exception("Image du schéma technique manquante : $schemaPath");
        }

        // Déterminer le format de l'image pour TCPDF
        $imageInfo = getimagesize($schemaPath);
        $imageFormat = 'PNG'; // Format par défaut
        
        if ($imageInfo !== false) {
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $imageFormat = 'JPG';
                    break;
                case IMAGETYPE_PNG:
                    $imageFormat = 'PNG';
                    break;
                case IMAGETYPE_GIF:
                    $imageFormat = 'GIF';
                    break;
            }
        }

        // Ajouter l'image directement dans le PDF - centrée et maximisée
        // Dimensions de la page A4 : 210mm x 297mm
        // Marges : 15mm de chaque côté
        $pageWidth = 210;
        $pageHeight = 297;
        $margin = 15;
        $availableWidth = $pageWidth - (2 * $margin); // 180mm
        $availableHeight = $pageHeight - (2 * $margin); // 267mm
        
        // Obtenir les dimensions réelles de l'image
        if ($imageInfo !== false) {
            $imageWidth = $imageInfo[0];
            $imageHeight = $imageInfo[1];
            $imageRatio = $imageWidth / $imageHeight;
            
            // Calculer la taille optimale en respectant l'homothétie
            $displayWidth = $availableWidth;
            $displayHeight = $displayWidth / $imageRatio;
            
            // Si la hauteur dépasse l'espace disponible, ajuster par la hauteur
            if ($displayHeight > $availableHeight) {
                $displayHeight = $availableHeight;
                $displayWidth = $displayHeight * $imageRatio;
            }
            
            // Centrer l'image
            $x = ($pageWidth - $displayWidth) / 2;
            $y = ($pageHeight - $displayHeight) / 2;
            
            $pdf->Image($schemaPath, $x, $y, $displayWidth, $displayHeight, $imageFormat, '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            // Fallback si getimagesize échoue
            $pdf->Image($schemaPath, 15, 15, 0, 267, $imageFormat, '', '', false, 300, '', false, false, 0, false, false, false);
        }

        // Gérer différemment selon le type de schéma
        if ($pdfSchema->getNom() === 'Pose en applique sans tapées') {
            // Pour pose en applique sans tapées : ajouter les 4 valeurs spécifiques
            $this->addAppliqueSansTapeesValues($pdf, $additionalValues ?: []);
        } elseif ($pdfSchema->getNom() === 'Pose tunnelle') {
            // Pour pose tunnelle : ajouter les 4 valeurs spécifiques
            $this->addPoseTunnelleValues($pdf, $additionalValues ?: []);
        } elseif ($pdfSchema->getNom() === 'Pose applique avec tapées isolation') {
            // Pour pose applique avec tapées isolation : ajouter largeur et tapées
            $this->addPoseAppliqueTapeesValues($pdf, $additionalValues ?: []);
        } elseif ($pdfSchema->getNom() === 'Pose en rénovation') {
            // Pour pose en rénovation : ajouter les dimensions dormant bois
            $this->addPoseRenovationValues($pdf, $additionalValues ?: []);
        } else {
            // Pour les autres schémas : ajouter les deux valeurs standard
            $this->addCustomValuesToPdf($pdf, $customValue, $calculatedValue);
        }

        // Ajouter les informations de configuration  
        $this->addConfigurationInfo($pdf, $confPf, $customValue, $pdfSchema, $additionalValues);

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
        $this->saveProjetPdf($confPf, $fileName, $customValue, $filePath, $pdfSchema);

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
        $pdf->Cell(35, 8, number_format($customValue, 0) . ' mm', 0, 0, 'C', false);
        $pdf->StopTransform();
        
        // Ajouter la côte extérieur - côté droit
        $pdf->StartTransform();
        $pdf->Rotate(90, $x2, $y2);
        $pdf->SetXY($x2, $y2);
        $pdf->Cell(35, 8, number_format($calculatedValue, 0) . ' mm', 0, 0, 'C', false);
        $pdf->StopTransform();
    }

    /**
     * Ajoute les 4 valeurs spécifiques pour la pose en applique sans tapées
     */
    private function addAppliqueSansTapeesValues(TCPDF $pdf, array $values): void
    {
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetTextColor(0, 0, 0); // Noir pour les côtes

        // Dimensions de la page A4 pour le calcul des positions
        $pageWidth = 210;
        $pageHeight = 297;
        $centerX = $pageWidth / 2;
        $centerY = $pageHeight / 2;

        // Positions relatives au coin supérieur gauche de la page (0,0)
        
        // Largeur Tableaux finis - en haut, centré
        if (isset($values['largeur_tableau'])) {
            $pdf->SetXY(63, 67.7); // Position centrée en haut
            $pdf->Cell(80, 6, (int)$values['largeur_tableau'] . ' mm', 0, 1, 'C');
        }

        // Hauteur Tableaux finis - position spécifique : 4cm du bord gauche, 17.5cm du haut
        if (isset($values['hauteur_tableau'])) {
            $xPosition = 109; // 4cm + 7cm - 0.2cm + 0.1cm = 10.9cm = 109mm du bord gauche
            $yPosition = 224; // 19cm + 5cm - 3cm + 1.5cm - 0.1cm = 22.4cm = 224mm du haut
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_tableau'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        
        if (isset($values['largeur_fabrication'])) {
            $pdf->SetXY(79, 113); 
            $pdf->Cell(80, 6, (int)$values['largeur_fabrication'] . ' mm', 0, 1, 'C');
        }

        
        if (isset($values['hauteur_fabrication'])) {
            $xPosition = 177; 
            $yPosition = 226; 
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_fabrication'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }
    }

    /**
     * Ajoute les valeurs spécifiques pour le schéma "Pose tunnelle"
     */
    private function addPoseTunnelleValues(TCPDF $pdf, array $values): void
    {
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Noir pour les côtes

        // Dimensions de la page A4 pour le calcul des positions
        $pageWidth = 210;
        $pageHeight = 297;
        $centerX = $pageWidth / 2;
        $centerY = $pageHeight / 2;

        // Positions relatives au coin supérieur gauche de la page (0,0)
        
        // Largeur Tableaux finis
        if (isset($values['largeur_tableau'])) {
            $pdf->SetXY(64, 53);
            $pdf->Cell(80, 6, (int)$values['largeur_tableau'] . ' mm', 0, 1, 'C');
        }

        // Hauteur Tableaux finis
        if (isset($values['hauteur_tableau'])) {
            $xPosition = 105;
            $yPosition = 234;
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_tableau'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        // Largeur Fabrication
        if (isset($values['largeur_tableau']) || isset($values['largeur_fabrication'])) {
            $fabricationWidth = null;
            if (isset($values['largeur_tableau'])) {
                $fabricationWidth = (float)$values['largeur_tableau'] - 10; // Consistance avec formulaire tunnelle
            }
            if ($fabricationWidth === null && isset($values['largeur_fabrication'])) {
                $fabricationWidth = (float)$values['largeur_fabrication'];
            }
            if ($fabricationWidth !== null) {
                $pdf->SetXY(77, 92);
                $pdf->Cell(80, 6, (int)$fabricationWidth . ' mm', 0, 1, 'C');
            }
        }

        // Hauteur Fabrication
        if (isset($values['hauteur_tableau']) || isset($values['hauteur_fabrication'])) {
            $fabricationHeight = null;
            if (isset($values['hauteur_tableau'])) {
                $fabricationHeight = (float)$values['hauteur_tableau'] - 10; // Consistance avec formulaire tunnelle
            }
            if ($fabricationHeight === null && isset($values['hauteur_fabrication'])) {
                $fabricationHeight = (float)$values['hauteur_fabrication'];
            }
            if ($fabricationHeight !== null) {
                $xPosition = 179;
                $yPosition = 216;
                
                $pdf->StartTransform();
                $pdf->Rotate(90, $xPosition, $yPosition);
                $pdf->SetXY($xPosition, $yPosition - 40);
                $pdf->Cell(80, 6, (int)$fabricationHeight . ' mm', 0, 1, 'C');
                $pdf->StopTransform();
            }
        }
    }

    /**
     * Ajoute les valeurs spécifiques pour le schéma "Pose applique avec tapées isolation"
     */
    private function addPoseAppliqueTapeesValues(TCPDF $pdf, array $values): void
    {
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Noir pour les côtes

        // Largeur Tableaux finis (position horizontale en haut)
        if (isset($values['largeur_tableau'])) {
            $pdf->SetXY(61, 58);
            $pdf->Cell(80, 6, (int)$values['largeur_tableau'] . ' mm', 0, 1, 'C');
        }

        // Hauteur Tableaux finis (position verticale à gauche avec rotation)
        if (isset($values['hauteur_tableau'])) {
            $xPosition = 124; // Position X à gauche
            $yPosition = 230; // Position Y pour la hauteur
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition);
            $pdf->Cell(80, 6, (int)$values['hauteur_tableau'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        // Largeur Fabrication (position horizontale en bas)
        if (isset($values['largeur_fabrication'])) {
            $pdf->SetXY(60, 107);
            $pdf->Cell(80, 6, (int)$values['largeur_fabrication'] . ' mm', 0, 1, 'C');
        }

        // Hauteur Fabrication (position verticale à droite avec rotation)
        if (isset($values['hauteur_fabrication'])) {
            $xPosition = 222; // Position X à droite
            $yPosition = 212; // Position Y pour la hauteur
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_fabrication'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        // Note: Les valeurs de tapées sont affichées dans la section "Informations de configuration"
    }

    /**
     * Ajoute les valeurs spécifiques pour le schéma "Pose en rénovation"
     */
    private function addPoseRenovationValues(TCPDF $pdf, array $values): void
    {
        // Définir la police
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(0, 0, 0); // Noir pour les côtes

        // Largeur entre dormant bois (position horizontale en haut)
        if (isset($values['largeur_dormant_bois'])) {
            $pdf->SetXY(60, 56);
            $pdf->Cell(80, 6, (int)$values['largeur_dormant_bois'] . ' mm', 0, 1, 'C');
        }

        // Hauteur dormant bois (position verticale à gauche avec rotation)
        if (isset($values['hauteur_dormant_bois'])) {
            $xPosition = 135; // Position X à gauche
            $yPosition = 224; // Position Y pour la hauteur
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_dormant_bois'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        // Largeur Fabrication (position horizontale en bas)
        if (isset($values['largeur_fabrication'])) {
            $pdf->SetXY(60, 99);
            $pdf->Cell(80, 6, (int)$values['largeur_fabrication'] . ' mm', 0, 1, 'C');
        }

        // Hauteur Fabrication (position verticale à droite avec rotation)
        if (isset($values['hauteur_fabrication'])) {
            $xPosition = 205; // Position X à droite
            $yPosition = 217; // Position Y pour la hauteur
            
            $pdf->StartTransform();
            $pdf->Rotate(90, $xPosition, $yPosition);
            $pdf->SetXY($xPosition, $yPosition - 40);
            $pdf->Cell(80, 6, (int)$values['hauteur_fabrication'] . ' mm', 0, 1, 'C');
            $pdf->StopTransform();
        }

        // Note: La particularité compensateur est affichée dans la section "Informations de configuration"
    }

    /**
     * Enregistre la relation PDF/Projet en base de données
     */
    private function saveProjetPdf(ConfPf $confPf, string $fileName, float $customValue, string $fullPath, PdfSchema $pdfSchema): void
    {
        $projetPdf = new ProjetPdf();
        $projetPdf->setProjet($confPf->getProjet());
        $projetPdf->setConfPf($confPf);
        $projetPdf->setPdfSchema($pdfSchema);
        $projetPdf->setFileName($fileName);
        $projetPdf->setFilePath('/uploads/pdf/' . $fileName);
        $projetPdf->setCustomValue($customValue);
        $projetPdf->setFileSize(filesize($fullPath));
        
        $this->entityManager->persist($projetPdf);
        $this->entityManager->flush();
    }

    private function addConfigurationInfo(TCPDF $pdf, ConfPf $confPf, float $customValue, ?PdfSchema $pdfSchema = null, ?array $additionalValues = null): void
    {
        $pdf->SetFont('helvetica', '', 7); // Police réduite
        $pdf->SetTextColor(0, 0, 0); // Noir pour les informations

        // Position dans le coin inférieur gauche
        $marginLeft = 10;
        $marginBottom = 30; // 10mm + 20mm (2cm) = 30mm de marge bas
        $pageHeight = 297; // Hauteur page A4
        
        // Calculer la position Y de départ (du bas vers le haut)
        $lineHeight = 4; // Hauteur de ligne réduite
        $nbLines = 10; // Nombre approximatif de lignes
        $y = $pageHeight - $marginBottom - ($nbLines * $lineHeight);

        // Titre
        $pdf->SetXY($marginLeft, $y);
        $pdf->SetFont('helvetica', 'B', 8); // Titre légèrement plus grand
        $pdf->Cell(0, 6, 'Informations de configuration', 0, 1, 'L');
        $y += 8;

        $pdf->SetFont('helvetica', '', 7); // Retour à la police normale

        // Informations du projet
        $infos = [
            'Projet: ' . $confPf->getProjet()->getRefClient(),
            'Produit: ' . ($confPf->getProduit() ? $confPf->getProduit()->getNom() : 'Non défini'),
            'Catégorie: ' . ($confPf->getCategorie() ? $confPf->getCategorie()->getNom() : 'Non définie'),
            'Sous-catégorie: ' . ($confPf->getSousCategorie() ? $confPf->getSousCategorie()->getNom() : 'Non définie'),
            'Ouverture: ' . ($confPf->getOuverture() ? $confPf->getOuverture()->getNom() : 'Non définie'),
            'Fournisseur: ' . ($confPf->getFournisseur() ? $confPf->getFournisseur()->getMarque() : 'Non défini'),
            'Système: ' . ($confPf->getSysteme() ? $confPf->getSysteme()->getNom() : 'Non défini'),
            'Type fenêtre/porte: ' . ($confPf->getTypeFenetrePorte() ? $confPf->getTypeFenetrePorte()->getNom() : 'Non défini'),
            'Vitrage: ' . ($confPf->getVitrage() ? $confPf->getVitrage()->getType() . ($confPf->getVitrage()->getRw() ? ' - RW: ' . $confPf->getVitrage()->getRw() : '') . ($confPf->getVitrage()->getEpaisseur() ? ' - Épaisseur: ' . $confPf->getVitrage()->getEpaisseur() : '') : 'Non défini'),
            'Quantité: ' . ($confPf->getQuantite() ?: 'Non définie'),
        ];
        
        // Ajouter les valeurs de tapées si c'est une pose applique avec tapées isolation
        if ($pdfSchema->getNom() === 'Pose applique avec tapées isolation') {
            if (isset($additionalValues['tapees_largeur'])) {
                $infos[] = 'Tapées Largeur: ' . number_format($additionalValues['tapees_largeur'], 0) . ' mm';
            }
            if (isset($additionalValues['tapees_hauteur'])) {
                $infos[] = 'Tapées Hauteur: ' . number_format($additionalValues['tapees_hauteur'], 0) . ' mm';
            }
        }
        
        // Ajouter la particularité compensateur si c'est une pose en rénovation
        if ($pdfSchema && $pdfSchema->getNom() === 'Pose en rénovation' && $additionalValues) {
            if (isset($additionalValues['particularite_compensateur']) && $additionalValues['particularite_compensateur']) {
                $infos[] = 'Particularité: Compensateur 10mm';
            }
        }
        
        $infos[] = 'Date de génération: ' . date('d/m/Y H:i');

        foreach ($infos as $info) {
            $pdf->SetXY($marginLeft, $y);
            $pdf->Cell(0, $lineHeight, $info, 0, 1, 'L');
            $y += $lineHeight;
        }
        
        // Ajouter le cadre "Dimensions commande" si les dimensions de fabrication sont disponibles
        if ($additionalValues && isset($additionalValues['largeur_fabrication']) && isset($additionalValues['hauteur_fabrication'])) {
            $y += 2; // Espacement
            
            // Dessiner un cadre
            $pdf->SetDrawColor(0, 0, 0); // Noir
            $pdf->SetFillColor(255, 255, 220); // Jaune clair
            $boxWidth = 70;
            $boxHeight = 14;
            $pdf->Rect($marginLeft, $y, $boxWidth, $boxHeight, 'DF');
            
            // Titre du cadre
            $pdf->SetXY($marginLeft + 2, $y + 1);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(0, 4, 'Dimensions commande', 0, 1, 'L');
            
            // Dimensions
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetXY($marginLeft + 2, $y + 5);
            $pdf->Cell(0, 4, 'Largeur: ' . number_format($additionalValues['largeur_fabrication'], 0) . ' mm', 0, 1, 'L');
            $pdf->SetXY($marginLeft + 2, $y + 9);
            $pdf->Cell(0, 4, 'Hauteur: ' . number_format($additionalValues['hauteur_fabrication'], 0) . ' mm', 0, 1, 'L');
        }
    }
}