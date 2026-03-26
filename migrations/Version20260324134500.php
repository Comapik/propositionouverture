<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter le nom du pack SAV
 */
final class Version20260324134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne nom dans la table Option_pack_SAV';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Option_pack_SAV ADD nom VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE Option_pack_SAV DROP nom');
    }
}
