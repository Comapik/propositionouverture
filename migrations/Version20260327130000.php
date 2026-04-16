<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Réamorçage des tables de référence volet vidées par les migrations précédentes.
 * Toutes les insertions sont idempotentes (WHERE NOT EXISTS).
 */
final class Version20260327130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réamorçage des données de référence : nuancier_standard, Tablier, type_coulisse, teinte_encadrement_elargi, teinte_encadrement_specifique, Option_pack_SAV, Caisson_PVC';
    }

    public function up(Schema $schema): void
    {
        // ── nuancier_standard (teintes tablier Bubendorff) ──────────────────────
        $nuanciers = [
            '100 - Blanc (RAL9016)',
            '105 - Gris clair (RAL 7035)',
            '112 - Aluminium gris (RAL9007)',
            '115 - Encadrement Aluminium clair (RAL 9006), tablier teinte aluminium métallisé',
            '117 - Gris anthracite (RAL 7016)',
            '119 - Gris quartz (RAL 7039)',
            '120 - Noir foncé (RAL 9005)',
            '125 - Gris terre d\'ombre (RAL 7022)',
            '150 - Rouge pourpre RAL 3004',
            '225 - Blanc perlé (RAL 1013)',
            '240 - Brun Sépia (RAL 8014)',
            '310 - Chêne doré',
            '403 - Noir sablé (Akzo 2100S)',
            '407 - Gris sablé (Akzo 2900S)',
        ];
        foreach ($nuanciers as $nom) {
            $this->addSql(
                "INSERT INTO nuancier_standard (nom) SELECT :nom WHERE NOT EXISTS (SELECT 1 FROM nuancier_standard WHERE nom = :nom)",
                ['nom' => $nom]
            );
        }

        // ── Tablier ─────────────────────────────────────────────────────────────
        $tabliers = ['DP368'];
        foreach ($tabliers as $type) {
            $this->addSql(
                "INSERT INTO Tablier (`type`) SELECT :type WHERE NOT EXISTS (SELECT 1 FROM Tablier WHERE `type` = :type)",
                ['type' => $type]
            );
        }

        // ── type_coulisse ────────────────────────────────────────────────────────
        $coulisses = ['AF', 'AL', 'A2', 'P3', 'U4', 'U6'];
        foreach ($coulisses as $nom) {
            $this->addSql(
                "INSERT INTO type_coulisse (nom) SELECT :nom WHERE NOT EXISTS (SELECT 1 FROM type_coulisse WHERE nom = :nom)",
                ['nom' => $nom]
            );
        }

        // ── teinte_encadrement_elargi ────────────────────────────────────────────
        $teinteElargi = [
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
        foreach ($teinteElargi as $nom) {
            $this->addSql(
                "INSERT INTO teinte_encadrement_elargi (nom) SELECT :nom WHERE NOT EXISTS (SELECT 1 FROM teinte_encadrement_elargi WHERE nom = :nom)",
                ['nom' => $nom]
            );
        }

        // ── teinte_encadrement_specifique ────────────────────────────────────────
        $teinteSpecifique = [
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
        foreach ($teinteSpecifique as $nom) {
            $this->addSql(
                "INSERT INTO teinte_encadrement_specifique (nom) SELECT :nom WHERE NOT EXISTS (SELECT 1 FROM teinte_encadrement_specifique WHERE nom = :nom)",
                ['nom' => $nom]
            );
        }

        // ── Option_pack_SAV ──────────────────────────────────────────────────────
        $packSav = [
            'Aucun',
            'P05 : pack SAV A de 5 ans',
            'P07 : pack SAV B de 7 ans',
            'P10 : pack SAV C de 10 ans',
        ];
        foreach ($packSav as $nom) {
            $this->addSql(
                "INSERT INTO Option_pack_SAV (nom) SELECT :nom WHERE NOT EXISTS (SELECT 1 FROM Option_pack_SAV WHERE nom = :nom)",
                ['nom' => $nom]
            );
        }

        // ── Caisson_PVC ──────────────────────────────────────────────────────────
        $caissons = ['BN th', 'BR th'];
        foreach ($caissons as $bloc) {
            $this->addSql(
                "INSERT INTO Caisson_PVC (bloc) SELECT :bloc WHERE NOT EXISTS (SELECT 1 FROM Caisson_PVC WHERE bloc = :bloc)",
                ['bloc' => $bloc]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM nuancier_standard WHERE nom IN (
            '100 - Blanc (RAL9016)', '105 - Gris clair (RAL 7035)', '112 - Aluminium gris (RAL9007)',
            '115 - Encadrement Aluminium clair (RAL 9006), tablier teinte aluminium métallisé',
            '117 - Gris anthracite (RAL 7016)', '119 - Gris quartz (RAL 7039)', '120 - Noir foncé (RAL 9005)',
            '125 - Gris terre d''ombre (RAL 7022)', '150 - Rouge pourpre RAL 3004', '225 - Blanc perlé (RAL 1013)',
            '240 - Brun Sépia (RAL 8014)', '310 - Chêne doré', '403 - Noir sablé (Akzo 2100S)', '407 - Gris sablé (Akzo 2900S)'
        )");
        $this->addSql("DELETE FROM Tablier WHERE `type` = 'DP368'");
        $this->addSql("DELETE FROM type_coulisse WHERE nom IN ('AF', 'AL', 'A2', 'P3', 'U4', 'U6')");
        $this->addSql("DELETE FROM teinte_encadrement_elargi WHERE nom LIKE 'teinte.%' OR nom LIKE '%RAL%' OR nom IN ('9010 - Blanc pur', 'teinte.9011')");
        $this->addSql("DELETE FROM teinte_encadrement_specifique WHERE nom LIKE 'teinte.%' OR nom LIKE '%RAL%' OR nom LIKE '%Polyester%'");
        $this->addSql("DELETE FROM Option_pack_SAV WHERE nom IN ('Aucun', 'P05 : pack SAV A de 5 ans', 'P07 : pack SAV B de 7 ans', 'P10 : pack SAV C de 10 ans')");
        $this->addSql("DELETE FROM Caisson_PVC WHERE bloc IN ('BN th', 'BR th')");
    }
}
