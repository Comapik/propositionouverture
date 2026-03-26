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
        // On joint KEY_COLUMN_USAGE avec TABLE_CONSTRAINTS pour ne cibler que
        // les FK qui existent réellement comme contraintes (évite les entrées
        // orphelines dans les métadonnées MySQL qui feraient échouer le DROP).
        $references = $this->connection->fetchAllAssociative(
            "SELECT kcu.TABLE_NAME, kcu.COLUMN_NAME, kcu.CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
             INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                 ON  tc.TABLE_SCHEMA    = kcu.TABLE_SCHEMA
                 AND tc.TABLE_NAME      = kcu.TABLE_NAME
                 AND tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                 AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
             WHERE kcu.TABLE_SCHEMA = DATABASE()
               AND kcu.REFERENCED_TABLE_NAME = 'extension_offre'"
        );

        foreach ($references as $reference) {
            $tableName      = str_replace('`', '``', (string) $reference['TABLE_NAME']);
            $columnName     = str_replace('`', '``', (string) $reference['COLUMN_NAME']);
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
