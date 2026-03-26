<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325141000 extends AbstractMigration
{
    private const NOMS = [
        'teinte.1000 -',
        'teinte.1001 -',
        'teinte.1004 -',
        'teinte.1035 -',
        'teinte.2001 -',
        'teinte.3000 -',
        'teinte.4005 -',
        'teinte.5002 -',
        'teinte.5005 -',
        'teinte.5007 -',
        'teinte.5008 -',
        'teinte.5009 -',
        'teinte.5011 -',
        'teinte.5012 -',
        'teinte.5014 -',
        'teinte.5015 -',
        'teinte.5017 -',
        '6003 - Vert Olive - RAL 6003 texturé AKZO NOBEL Interpon Structura D2525',
        'teinte.6012 -',
        'teinte.6017 -',
        'teinte.6019 -',
        'teinte.6020 -',
        'teinte.6025 -',
        'teinte.6027 -',
        'teinte.6029 -',
        'teinte.7000 -',
        'teinte.7003 -',
        'teinte.7004 -',
        'teinte.7005 -',
        'teinte.7008 -',
        'teinte.7010 -',
        'teinte.7011 -',
        'teinte.7023 -',
        'teinte.7026 -',
        'teinte.7031 -',
        'teinte.7032 -',
        'teinte.7033 -',
        '7034 - Gris Jaune - RAL 7034 texturé AKZO NOBEL Interpon Structura D2525',
        'teinte.7036 -',
        '7038 - Gris Agate - RAL 7038 texturé AKZO NOBEL Interpon Structura D2525',
        'teinte.7042 -',
        'teinte.7044 -',
        'teinte.7045 -',
        'teinte.7048 -',
        'teinte.8000 -',
        'teinte.8003 -',
        'teinte.8007 -',
        'teinte.8016 -',
        'teinte.8017 -',
        'teinte.8022 -',
        'teinte.8025 -',
        'teinte.8028 -',
        'teinte.9002 -',
        'teinte.9003 -',
        'teinte.9004 -',
        'teinte.9017 -',
        'teinte.9018 -',
        'teinte.9022 -',
        'teinte.0402 - CANON, Polyester HD D2525, Lisse, Mat',
        'teinte.0405 - GALET, Polyester HD D2525, Lisse, Brillant',
        'teinte.0406 - GRIS 2150, Polyester HD D2525, Sablé, Mat',
        'teinte.0526 - PYRITE (CA RAL 9007), Polyester HD D2525, Lisse, Mat 20 %',
        'teinte.0594 - SILVER (CA RAL 9006), Polyester HD D2525, Lisse, Mat 20 %',
        'teinte.0634 - BRUN 2650, Polyester HD D2525, Sablé, Mat',
        'teinte.0661 - BLEU 2700, Polyester HD D2525, Sablé, Mat',
        'teinte.0694 - GRIS 2400, Polyester HD D2525, Sablé, Mat',
        'teinte.0729 - GRIS 2800, Polyester HD D2525, Sablé, Mat',
        'teinte.0844 - INNOKO, Polyester HD D2525, Sablé, Mat',
        'teinte.0850 - Interpon D1036 Matt - DB702',
        'teinte.0852 - Hard Nickel Matt D2525',
        'teinte.1036 -',
    ];

    public function getDescription(): string
    {
        return 'Insere les teintes dans teinte_encadrement_specifique';
    }

    public function up(Schema $schema): void
    {
        foreach (self::NOMS as $nom) {
            $exists = $this->connection->fetchOne(
                'SELECT id FROM teinte_encadrement_specifique WHERE nom = :nom LIMIT 1',
                ['nom' => $nom]
            );

            if ($exists === false || $exists === null) {
                $this->addSql(
                    'INSERT INTO teinte_encadrement_specifique (nom) VALUES (:nom)',
                    ['nom' => $nom]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM teinte_encadrement_specifique WHERE nom IN (:noms)',
            ['noms' => self::NOMS],
            ['noms' => ArrayParameterType::STRING]
        );
    }
}
