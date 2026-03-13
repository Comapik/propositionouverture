<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Populate aeration with position associations
 */
final class Version20260207153813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate aeration with position associations';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les données actuelles
        $this->addSql('DELETE FROM aeration');
        
        // Réinsérer les aérateurs avec les positions correctes
        // Position 1 = Gauche, 2 = Centre, 3 = Droit
        $this->addSql("INSERT INTO aeration (modele, position_id) VALUES 
            ('Aérateur discret', 1),
            ('Aérateur standard', 1),
            ('Aérateur hygroréglable', 1),
            ('Aérateur discret', 2),
            ('Aérateur standard', 2),
            ('Aérateur latéral', 3),
            ('Aérateur d''angle', 3)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM aeration');
    }
}
