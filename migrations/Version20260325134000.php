<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325134000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Supprime la table extension_offre et toutes les colonnes FK qui la referencent';
    }

    public function up(Schema $schema): void
    {
        $references = $this->connection->fetchAllAssociative(
            "SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME = 'extension_offre'"
        );

        foreach ($references as $reference) {
            $tableName = str_replace('`', '``', (string) $reference['TABLE_NAME']);
            $columnName = str_replace('`', '``', (string) $reference['COLUMN_NAME']);
            $constraintName = str_replace('`', '``', (string) $reference['CONSTRAINT_NAME']);

            $this->addSql(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $tableName,
                $constraintName
            ));

            $this->addSql(sprintf(
                'ALTER TABLE `%s` DROP COLUMN `%s`',
                $tableName,
                $columnName
            ));
        }

        $this->addSql('DROP TABLE IF EXISTS `extension_offre`');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigration('La suppression de extension_offre est irreversible (structure et donnees).');
    }
}
