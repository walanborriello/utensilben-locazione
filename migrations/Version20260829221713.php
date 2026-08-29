<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260829221713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rimuove i codici EAN/barcode: ridondanti con il codice articolo del prodotto.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE materiale_codice DROP FOREIGN KEY FK_F82E721A4FEDC103');
        $this->addSql('DROP TABLE materiale_codice');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE materiale_codice (id INT AUTO_INCREMENT NOT NULL, materiale_id INT NOT NULL, codice VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, tipo VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX uniq_materiale_codice_valore (codice), INDEX IDX_F82E721A4FEDC103 (materiale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE materiale_codice ADD CONSTRAINT FK_F82E721A4FEDC103 FOREIGN KEY (materiale_id) REFERENCES materiale (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
