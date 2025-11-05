<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Consolidated Migration - All tables and relationships
 */
final class Version20251104225713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Consolidated migration containing all database schema changes';
    }

    public function up(Schema $schema): void
    {
        // Create clients table
        $this->addSql('CREATE TABLE clients (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, tel VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create messenger_messages table
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create projets table
        $this->addSql('CREATE TABLE projets (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, ref_client VARCHAR(255) NOT NULL, lieu VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', description VARCHAR(255) DEFAULT NULL, conf_pf_id INT DEFAULT NULL, conf_volet_id INT DEFAULT NULL, INDEX IDX_B454C1DB19EB6921 (client_id), UNIQUE INDEX UNIQ_B454C1DBF4C0BC36 (conf_pf_id), UNIQUE INDEX UNIQ_B454C1DBD143FC2B (conf_volet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create user table
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create produits table
        $this->addSql('CREATE TABLE produits (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create sous_categories table
        $this->addSql('CREATE TABLE sous_categories (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_DC8B1382F347EFB (produit_id), INDEX IDX_DC8B1382BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create materiaux table
        $this->addSql('CREATE TABLE materiaux (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create ouverture table
        $this->addSql('CREATE TABLE ouverture (id INT AUTO_INCREMENT NOT NULL, sous_categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_43461EAB365BF48 (sous_categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create couleurs table
        $this->addSql('CREATE TABLE couleurs (id INT AUTO_INCREMENT NOT NULL, plaxage_laquage_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_CB52D47BCBC9DEE (plaxage_laquage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create petit_bois table
        $this->addSql('CREATE TABLE petit_bois (id INT AUTO_INCREMENT NOT NULL, taille_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_B064EB69FF25611A (taille_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create vitrage table
        $this->addSql('CREATE TABLE vitrage (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create fournisseurs table
        $this->addSql('CREATE TABLE fournisseurs (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create pose table
        $this->addSql('CREATE TABLE pose (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create petit_bois_colle table
        $this->addSql('CREATE TABLE petit_bois_colle (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create petit_bois_incorpore table
        $this->addSql('CREATE TABLE petit_bois_incorpore (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create taille_petit_bois table
        $this->addSql('CREATE TABLE taille_petit_bois (id INT AUTO_INCREMENT NOT NULL, taille VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create plaxage_laquage table
        $this->addSql('CREATE TABLE plaxage_laquage (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create conf_pf table
        $this->addSql('CREATE TABLE conf_pf (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, sous_categorie_id INT DEFAULT NULL, overture_id INT DEFAULT NULL, materiau_id INT DEFAULT NULL, petit_bois_id INT DEFAULT NULL, vitrage_id INT DEFAULT NULL, fournisseur_id INT DEFAULT NULL, pose_id INT DEFAULT NULL, couleur_int_id INT DEFAULT NULL, couleur_ext_id INT DEFAULT NULL, qauntite INT DEFAULT NULL, hauteur DOUBLE PRECISION DEFAULT NULL, largeur DOUBLE PRECISION DEFAULT NULL, cote_tableau_fini DOUBLE PRECISION DEFAULT NULL, cote_hors_tout DOUBLE PRECISION DEFAULT NULL, plan VARCHAR(255) DEFAULT NULL, hauteur_poignee DOUBLE PRECISION DEFAULT NULL, piece_appuie TINYINT(1) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, photos VARCHAR(255) DEFAULT NULL, elargisseur_dormant VARCHAR(255) DEFAULT NULL, tape VARCHAR(255) DEFAULT NULL, INDEX IDX_AD1816CF347EFB (produit_id), INDEX IDX_AD1816C365BF48 (sous_categorie_id), INDEX IDX_AD1816CFE5FB43D (overture_id), INDEX IDX_AD1816CCE19B47A (materiau_id), INDEX IDX_AD1816CD612BA49 (petit_bois_id), INDEX IDX_AD1816CC6B2988F (vitrage_id), INDEX IDX_AD1816C670C757F (fournisseur_id), INDEX IDX_AD1816CA32C33D6 (pose_id), INDEX IDX_AD1816C2C15D40F (couleur_int_id), INDEX IDX_AD1816CB4777656 (couleur_ext_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create ligne_commande table
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, repere VARCHAR(255) DEFAULT NULL, nombre INT DEFAULT NULL, largeur DOUBLE PRECISION DEFAULT NULL, hauteur DOUBLE PRECISION DEFAULT NULL, multi_system VARCHAR(255) DEFAULT NULL, lame_orientable TINYINT(1) DEFAULT NULL, implentation VARCHAR(255) DEFAULT NULL, taille_caison DOUBLE PRECISION DEFAULT NULL, type_manoeuvre VARCHAR(255) DEFAULT NULL, cote_manoeuvre DOUBLE PRECISION DEFAULT NULL, cable5m TINYINT(1) DEFAULT NULL, panneau_pv_deporte TINYINT(1) DEFAULT NULL, moustiquaire TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create options_moteur_radio table
        $this->addSql('CREATE TABLE options_moteur_radio (id INT AUTO_INCREMENT NOT NULL, cmg VARCHAR(255) DEFAULT NULL, h4c TINYINT(1) DEFAULT NULL, di4 TINYINT(1) DEFAULT NULL, smu TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create conf_volet table
        $this->addSql('CREATE TABLE conf_volet (id INT AUTO_INCREMENT NOT NULL, ligne_commande_id INT DEFAULT NULL, options_moteur_radio_id INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, extension_offre TINYINT(1) DEFAULT NULL, tablier VARCHAR(255) DEFAULT NULL, specificite_caisson VARCHAR(255) DEFAULT NULL, option_pack_sav TINYINT(1) DEFAULT NULL, livraison_flash TINYINT(1) DEFAULT NULL, photos VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_91A702BDE10FEE63 (ligne_commande_id), UNIQUE INDEX UNIQ_91A702BDE21CC806 (options_moteur_radio_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create categorie table
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, produits_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_497DD634CD11A2CF (produits_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Create systeme_capotage table
        $this->addSql('CREATE TABLE systeme_capotage (id INT AUTO_INCREMENT NOT NULL, fournisseur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_EE25737A670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add Foreign Keys
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DB19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DBF4C0BC36 FOREIGN KEY (conf_pf_id) REFERENCES conf_pf (id)');
        $this->addSql('ALTER TABLE projets ADD CONSTRAINT FK_B454C1DBD143FC2B FOREIGN KEY (conf_volet_id) REFERENCES conf_volet (id)');
        
        $this->addSql('ALTER TABLE sous_categories ADD CONSTRAINT FK_DC8B1382F347EFB FOREIGN KEY (produit_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE sous_categories ADD CONSTRAINT FK_DC8B1382BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        
        $this->addSql('ALTER TABLE ouverture ADD CONSTRAINT FK_43461EAB365BF48 FOREIGN KEY (sous_categorie_id) REFERENCES sous_categories (id)');
        
        $this->addSql('ALTER TABLE couleurs ADD CONSTRAINT FK_CB52D47BCBC9DEE FOREIGN KEY (plaxage_laquage_id) REFERENCES plaxage_laquage (id)');
        
        $this->addSql('ALTER TABLE petit_bois ADD CONSTRAINT FK_B064EB69FF25611A FOREIGN KEY (taille_id) REFERENCES taille_petit_bois (id)');
        
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CF347EFB FOREIGN KEY (produit_id) REFERENCES produits (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C365BF48 FOREIGN KEY (sous_categorie_id) REFERENCES sous_categories (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CFE5FB43D FOREIGN KEY (overture_id) REFERENCES ouverture (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CCE19B47A FOREIGN KEY (materiau_id) REFERENCES materiaux (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CD612BA49 FOREIGN KEY (petit_bois_id) REFERENCES petit_bois (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CC6B2988F FOREIGN KEY (vitrage_id) REFERENCES vitrage (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CA32C33D6 FOREIGN KEY (pose_id) REFERENCES pose (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816C2C15D40F FOREIGN KEY (couleur_int_id) REFERENCES couleurs (id)');
        $this->addSql('ALTER TABLE conf_pf ADD CONSTRAINT FK_AD1816CB4777656 FOREIGN KEY (couleur_ext_id) REFERENCES couleurs (id)');
        
        $this->addSql('ALTER TABLE conf_volet ADD CONSTRAINT FK_91A702BDE10FEE63 FOREIGN KEY (ligne_commande_id) REFERENCES ligne_commande (id)');
        $this->addSql('ALTER TABLE conf_volet ADD CONSTRAINT FK_91A702BDE21CC806 FOREIGN KEY (options_moteur_radio_id) REFERENCES options_moteur_radio (id)');
        
        $this->addSql('ALTER TABLE categorie ADD CONSTRAINT FK_497DD634CD11A2CF FOREIGN KEY (produits_id) REFERENCES produits (id)');
        
        $this->addSql('ALTER TABLE systeme_capotage ADD CONSTRAINT FK_EE25737A670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop all foreign keys first
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DB19EB6921');
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DBF4C0BC36');
        $this->addSql('ALTER TABLE projets DROP FOREIGN KEY FK_B454C1DBD143FC2B');
        
        $this->addSql('ALTER TABLE sous_categories DROP FOREIGN KEY FK_DC8B1382F347EFB');
        $this->addSql('ALTER TABLE sous_categories DROP FOREIGN KEY FK_DC8B1382BCF5E72D');
        
        $this->addSql('ALTER TABLE ouverture DROP FOREIGN KEY FK_43461EAB365BF48');
        
        $this->addSql('ALTER TABLE couleurs DROP FOREIGN KEY FK_CB52D47BCBC9DEE');
        
        $this->addSql('ALTER TABLE petit_bois DROP FOREIGN KEY FK_B064EB69FF25611A');
        
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CF347EFB');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C365BF48');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CFE5FB43D');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CCE19B47A');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CD612BA49');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CC6B2988F');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C670C757F');
        $this->addSql('ALTER TABLE conf_pf DROP CONSTRAINT FK_AD1816CA32C33D6');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816C2C15D40F');
        $this->addSql('ALTER TABLE conf_pf DROP FOREIGN KEY FK_AD1816CB4777656');
        
        $this->addSql('ALTER TABLE conf_volet DROP FOREIGN KEY FK_91A702BDE10FEE63');
        $this->addSql('ALTER TABLE conf_volet DROP FOREIGN KEY FK_91A702BDE21CC806');
        
        $this->addSql('ALTER TABLE categorie DROP FOREIGN KEY FK_497DD634CD11A2CF');
        
        $this->addSql('ALTER TABLE systeme_capotage DROP FOREIGN KEY FK_EE25737A670C757F');
        
        // Drop all tables
        $this->addSql('DROP TABLE projets');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE sous_categories');
        $this->addSql('DROP TABLE produits');
        $this->addSql('DROP TABLE ouverture');
        $this->addSql('DROP TABLE materiaux');
        $this->addSql('DROP TABLE couleurs');
        $this->addSql('DROP TABLE petit_bois');
        $this->addSql('DROP TABLE vitrage');
        $this->addSql('DROP TABLE fournisseurs');
        $this->addSql('DROP TABLE pose');
        $this->addSql('DROP TABLE petit_bois_colle');
        $this->addSql('DROP TABLE petit_bois_incorpore');
        $this->addSql('DROP TABLE taille_petit_bois');
        $this->addSql('DROP TABLE plaxage_laquage');
        $this->addSql('DROP TABLE conf_pf');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE options_moteur_radio');
        $this->addSql('DROP TABLE conf_volet');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE systeme_capotage');
    }
}
