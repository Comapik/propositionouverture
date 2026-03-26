<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert teintes tablier data into Teintes_tablier_volet';
    }

    public function up(Schema $schema): void
    {
        $teintes = [
            '100 - Blanc (RAL9016)',
            '105 - Gris clair (RAL 7035)',
            '112 - Aluminium gris (RAL9007)',
            '115 - Encadrement Aluminium clair (RAL 9006), tablier teinte aluminium métallisé',
            '117 - Gris anthracite (RAL 7016)',
            '119 - Gris quartz (RAL 7039)',
            '120 - Noir foncé (RAL 9005)',
            '125 - Gris terre d\'ombre (RAL 7022)',
            '150 - Rouge pourpre RAL 3004',
            '225 - Blanc perlé (RAL 1013)',
            '240 - Brun Sépia (RAL 8014)',
            '310 - Chêne doré',
            '403 - Noir sablé (Akzo 2100S)',
            '407 - Gris sablé (Akzo 2900S)',
        ];

        foreach ($teintes as $nom) {
            $this->addSql('INSERT INTO Teintes_tablier_volet (nom) VALUES (:nom)', ['nom' => $nom]);
        }
    }

    public function down(Schema $schema): void
    {
        $teintes = [
            '100 - Blanc (RAL9016)',
            '105 - Gris clair (RAL 7035)',
            '112 - Aluminium gris (RAL9007)',
            '115 - Encadrement Aluminium clair (RAL 9006), tablier teinte aluminium métallisé',
            '117 - Gris anthracite (RAL 7016)',
            '119 - Gris quartz (RAL 7039)',
            '120 - Noir foncé (RAL 9005)',
            '125 - Gris terre d\'ombre (RAL 7022)',
            '150 - Rouge pourpre RAL 3004',
            '225 - Blanc perlé (RAL 1013)',
            '240 - Brun Sépia (RAL 8014)',
            '310 - Chêne doré',
            '403 - Noir sablé (Akzo 2100S)',
            '407 - Gris sablé (Akzo 2900S)',
        ];

        foreach ($teintes as $nom) {
            $this->addSql('DELETE FROM Teintes_tablier_volet WHERE nom = :nom', ['nom' => $nom]);
        }
    }
}
