<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325140000 extends AbstractMigration
{
    private const NOMS = [
        'teinte.0736 - AKZO NOBEL Futura 2014/2017 BLEU 2600 SABLE',
        'teinte.0404 - AKZO NOBEL Futura 2014/2017 NOIR 2200 SAB',
        'teinte.0408 - AKZO NOBEL Futura 2014/2017 MARS 2525 SABLE',
        'teinte.0624 - TIGER Structure Fine GRIS DB703 SABLE MAT',
        'teinte.0767 - AKZO NOBEL Futura 2014/2017 GRIS 2500 SABLE',
        '0822 - Brun 1247F - Ral 0822 texturé AXALTA 1247FSD AE03058124720',
        'teinte.1015 - Ivoire clair',
        '1019 - Beige Gris - RAL 1019 texturé AKZO NOBEL Interpon Structura D2525',
        '3005 - Rouge Vin - RAL 3005 texturé AKZO NOBEL Interpon Structura D2525',
        '5003 - Bleu Saphir - RAL 5003 texturé AKZO NOBEL Interpon Structura D2525',
        '5010 - Bleu Gentiane - RAL 5010 texturé AKZO NOBEL Interpon Structura D2525',
        '5023 - Bleu Distant - RAL 5023 texturé AKZO NOBEL Interpon Structura D2525',
        '5024 - Bleu Pastel - RAL 5024 texturé AKZO NOBEL Interpon Structura D2525',
        '6005 - Vert Mousse - RAL 6005 texturé AKZO NOBEL Interpon Structura D2525',
        '6009 - Vert Sapin - RAL 6009 texturé AKZO NOBEL Interpon Structura D2525',
        '6021 - Vert Pâle - RAL 6021 texturé AKZO NOBEL Interpon Structura D2525',
        '7001 - Gris Argent - RAL 7001 texturé AKZO NOBEL Interpon Structura D2525',
        '7006 - Gris Beige - RAL 7006 texturé AKZO NOBEL Interpon Structura D2525',
        '7012 - Gris Basalte - RAL 7012 texturé AKZO NOBEL Interpon Structura D2525',
        '7015 - Gris Ardoise - RAL 7015 texturé AKZO NOBEL Interpon Structura D2525',
        '7021 - Gris Noir - RAL 7021 texturé AKZO NOBEL Interpon Structura D2525',
        '7024 - Gris Graphite - RAL 7024 texturé AKZO NOBEL Interpon Structura D2525',
        '7030 - Gris Pierre - RAL 7030 texturé AKZO NOBEL Interpon Structura D2525',
        '7037 - Gris Poussière - RAL 7037 texturé AKZO NOBEL Interpon Structura D2525',
        '7040 - Gris Fenêtre - RAL 7040 texturé AKZO NOBEL Interpon Structura D2525',
        '7047 - Télégris 4 - RAL 7047 texturé AKZO NOBEL Interpon Structura D2525',
        '8011 - Brun Noisette - RAL 8011 texturé AKZO NOBEL Interpon Structura D2525',
        '8019 - Brun Gris - RAL 8019 texturé AKZO NOBEL Interpon Structura D2525',
        '9001 - Blanc Cassé - RAL 9001 texturé AKZO NOBEL Interpon Structura D2525',
        '9010 - Blanc pur',
        'teinte.9011',
    ];

    public function getDescription(): string
    {
        return 'Insere les teintes dans teinte_encadrement_elargi';
    }

    public function up(Schema $schema): void
    {
        foreach (self::NOMS as $nom) {
            $exists = $this->connection->fetchOne(
                'SELECT id FROM teinte_encadrement_elargi WHERE nom = :nom LIMIT 1',
                ['nom' => $nom]
            );

            if ($exists === false || $exists === null) {
                $this->addSql(
                    'INSERT INTO teinte_encadrement_elargi (nom) VALUES (:nom)',
                    ['nom' => $nom]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM teinte_encadrement_elargi WHERE nom IN (:noms)',
            ['noms' => self::NOMS],
            ['noms' => \Doctrine\DBAL\ArrayParameterType::STRING]
        );
    }
}
