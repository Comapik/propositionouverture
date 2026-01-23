<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251217121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déplace le champ sens_ouverture de type_fenetre_porte vers ouverture.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ouverture ADD sens_ouverture VARCHAR(10) DEFAULT NULL");
        $this->addSql("ALTER TABLE type_fenetre_porte DROP sens_ouverture");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE type_fenetre_porte ADD sens_ouverture VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE ouverture DROP sens_ouverture');
    }
}