<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260829180725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, fila_id INT NOT NULL, lato VARCHAR(20) NOT NULL, INDEX IDX_D7943D681CA9EE3B (fila_id), UNIQUE INDEX uniq_area_fila_lato (fila_id, lato), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(100) NOT NULL, UNIQUE INDEX uniq_categoria_nome (nome), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fila (id INT AUTO_INCREMENT NOT NULL, codice VARCHAR(20) NOT NULL, nome VARCHAR(100) NOT NULL, tipo VARCHAR(20) NOT NULL, UNIQUE INDEX uniq_fila_codice (codice), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE materiale (id INT AUTO_INCREMENT NOT NULL, categoria_id INT DEFAULT NULL, codice VARCHAR(50) NOT NULL, nome VARCHAR(150) NOT NULL, descrizione LONGTEXT DEFAULT NULL, unita_misura VARCHAR(20) DEFAULT NULL, immagine VARCHAR(255) DEFAULT NULL, attivo TINYINT(1) NOT NULL, INDEX IDX_6FC3A3E43397707A (categoria_id), INDEX idx_materiale_nome (nome), UNIQUE INDEX uniq_materiale_codice (codice), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE materiale_codice (id INT AUTO_INCREMENT NOT NULL, materiale_id INT NOT NULL, codice VARCHAR(50) NOT NULL, tipo VARCHAR(20) NOT NULL, INDEX IDX_F82E721A4FEDC103 (materiale_id), UNIQUE INDEX uniq_materiale_codice_valore (codice), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE piano (id INT AUTO_INCREMENT NOT NULL, area_id INT NOT NULL, numero INT NOT NULL, etichetta VARCHAR(100) DEFAULT NULL, INDEX IDX_16B834ACBD0F409C (area_id), UNIQUE INDEX uniq_piano_area_numero (area_id, numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posizionamento (id INT AUTO_INCREMENT NOT NULL, materiale_id INT NOT NULL, piano_id INT NOT NULL, principale TINYINT(1) NOT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_D316D7724FEDC103 (materiale_id), INDEX IDX_D316D772C8B3A96B (piano_id), UNIQUE INDEX uniq_posizionamento_materiale_piano (materiale_id, piano_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D681CA9EE3B FOREIGN KEY (fila_id) REFERENCES fila (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE materiale ADD CONSTRAINT FK_6FC3A3E43397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE materiale_codice ADD CONSTRAINT FK_F82E721A4FEDC103 FOREIGN KEY (materiale_id) REFERENCES materiale (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE piano ADD CONSTRAINT FK_16B834ACBD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posizionamento ADD CONSTRAINT FK_D316D7724FEDC103 FOREIGN KEY (materiale_id) REFERENCES materiale (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posizionamento ADD CONSTRAINT FK_D316D772C8B3A96B FOREIGN KEY (piano_id) REFERENCES piano (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D681CA9EE3B');
        $this->addSql('ALTER TABLE materiale DROP FOREIGN KEY FK_6FC3A3E43397707A');
        $this->addSql('ALTER TABLE materiale_codice DROP FOREIGN KEY FK_F82E721A4FEDC103');
        $this->addSql('ALTER TABLE piano DROP FOREIGN KEY FK_16B834ACBD0F409C');
        $this->addSql('ALTER TABLE posizionamento DROP FOREIGN KEY FK_D316D7724FEDC103');
        $this->addSql('ALTER TABLE posizionamento DROP FOREIGN KEY FK_D316D772C8B3A96B');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE categoria');
        $this->addSql('DROP TABLE fila');
        $this->addSql('DROP TABLE materiale');
        $this->addSql('DROP TABLE materiale_codice');
        $this->addSql('DROP TABLE piano');
        $this->addSql('DROP TABLE posizionamento');
    }
}
