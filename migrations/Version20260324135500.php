<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Seed des options pack SAV
 */
final class Version20260324135500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insertion des options pack SAV par defaut dans Option_pack_SAV';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO Option_pack_SAV (nom) SELECT 'Aucun' WHERE NOT EXISTS (SELECT 1 FROM Option_pack_SAV WHERE nom = 'Aucun')");
        $this->addSql("INSERT INTO Option_pack_SAV (nom) SELECT 'P05 : pack SAV A de 5 ans' WHERE NOT EXISTS (SELECT 1 FROM Option_pack_SAV WHERE nom = 'P05 : pack SAV A de 5 ans')");
        $this->addSql("INSERT INTO Option_pack_SAV (nom) SELECT 'P07 : pack SAV B de 7 ans' WHERE NOT EXISTS (SELECT 1 FROM Option_pack_SAV WHERE nom = 'P07 : pack SAV B de 7 ans')");
        $this->addSql("INSERT INTO Option_pack_SAV (nom) SELECT 'P10 : pack SAV C de 10 ans' WHERE NOT EXISTS (SELECT 1 FROM Option_pack_SAV WHERE nom = 'P10 : pack SAV C de 10 ans')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM Option_pack_SAV WHERE nom IN ('Aucun', 'P05 : pack SAV A de 5 ans', 'P07 : pack SAV B de 7 ans', 'P10 : pack SAV C de 10 ans')");
    }
}
