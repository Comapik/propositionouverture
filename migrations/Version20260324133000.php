<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter INV_avec_inverseur sur Option Moteur-Filaire_Bubendorff
 */
final class Version20260324133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne INV_avec_inverseur dans la table Option Moteur-Filaire_Bubendorff';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Option Moteur-Filaire_Bubendorff` ADD `INV_avec_inverseur` BINARY(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `Option Moteur-Filaire_Bubendorff` DROP `INV_avec_inverseur`');
    }
}
