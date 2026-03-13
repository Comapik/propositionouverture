<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260207155652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Créer la table conf_aeration si elle n'existe pas
        $connection = $this->connection;
        $schemaManager = $connection->createSchemaManager();
        
        if (!$schemaManager->tablesExist(['conf_aeration'])) {
            $this->addSql('CREATE TABLE conf_aeration (id INT AUTO_INCREMENT NOT NULL, aeration_id INT NOT NULL, position_id INT NOT NULL, INDEX IDX_597889C66140AA72 (aeration_id), INDEX IDX_597889C6DD842E46 (position_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE conf_aeration ADD CONSTRAINT FK_597889C66140AA72 FOREIGN KEY (aeration_id) REFERENCES aeration (id)');
            $this->addSql('ALTER TABLE conf_aeration ADD CONSTRAINT FK_597889C6DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        }
        
        // Ajouter la nouvelle colonne conf_aeration_id si elle n'existe pas
        $columns = $schemaManager->listTableColumns('conf_pf');
        $columnNames = array_keys($columns);
        
        if (!in_array('conf_aeration_id', $columnNames)) {
            if (in_array('aeration_id', $columnNames)) {
                // Renommer aeration_id en conf_aeration_id
                $this->addSql('ALTER TABLE conf_pf CHANGE aeration_id conf_aeration_id INT DEFAULT NULL');
            } else {
                // Ajouter conf_aeration_id
                $this->addSql('ALTER TABLE conf_pf ADD conf_aeration_id INT DEFAULT NULL');
            }
        }
        
        // Mettre à NULL les valeurs existantes temporairement
        $this->addSql('UPDATE conf_pf SET conf_aeration_id = NULL WHERE conf_aeration_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C47FCF597');
        $this->addSql('ALTER TABLE conf_aeration DROP FOREIGN KEY FK_597889C66140AA72');
        $this->addSql('ALTER TABLE conf_aeration DROP FOREIGN KEY FK_597889C6DD842E46');
        $this->addSql('DROP TABLE conf_aeration');
        $this->addSql('ALTER TABLE aeration ADD position_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE aeration ADD CONSTRAINT FK_A961126ADD842E46 FOREIGN KEY (position_id) REFERENCES position (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A961126ADD842E46 ON aeration (position_id)');
        $this->addSql('DROP INDEX IDX_AD1816C47FCF597 ON conf_pf');
        $this->addSql('ALTER TABLE conf_pf CHANGE conf_aeration_id aeration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CCC7324F2E FOREIGN KEY (aeration_id) REFERENCES aeration (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_AD1816C6140AA72 ON conf_pf (aeration_id)');
    }
}
