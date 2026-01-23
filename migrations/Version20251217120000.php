<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251217120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du sens d\'ouverture sur les types de porte et la configuration PF.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE conf_pf ADD sens_ouverture VARCHAR(10) DEFAULT NULL");
        $this->addSql("ALTER TABLE type_fenetre_porte ADD sens_ouverture VARCHAR(10) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conf_pf DROP sens_ouverture');
        $this->addSql('ALTER TABLE type_fenetre_porte DROP sens_ouverture');
    }
}
