<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914104705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ristruttura il modello di posizione: Piano (edificio) -> Fila (lettera) -> Sezione -> Ripiano, al posto di Fila (codice/tipo) -> Area (lato) -> Piano (numero).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE piano DROP FOREIGN KEY FK_16B834ACBD0F409C');
        $this->addSql('CREATE TABLE ripiano (id INT AUTO_INCREMENT NOT NULL, sezione_id INT NOT NULL, numero INT NOT NULL, etichetta VARCHAR(100) DEFAULT NULL, INDEX IDX_19FA835F81E679D8 (sezione_id), UNIQUE INDEX uniq_ripiano_sezione_numero (sezione_id, numero), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sezione (id INT AUTO_INCREMENT NOT NULL, fila_id INT NOT NULL, lettera VARCHAR(1) NOT NULL, INDEX IDX_68B494251CA9EE3B (fila_id), UNIQUE INDEX uniq_sezione_fila_lettera (fila_id, lettera), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ripiano ADD CONSTRAINT FK_19FA835F81E679D8 FOREIGN KEY (sezione_id) REFERENCES sezione (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sezione ADD CONSTRAINT FK_68B494251CA9EE3B FOREIGN KEY (fila_id) REFERENCES fila (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D681CA9EE3B');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP INDEX uniq_fila_codice ON fila');
        $this->addSql('ALTER TABLE fila ADD piano_id INT NOT NULL, ADD lettera VARCHAR(1) NOT NULL, ADD prima_fila TINYINT(1) NOT NULL, ADD ultima_fila TINYINT(1) NOT NULL, DROP codice, DROP nome, DROP tipo');
        $this->addSql('ALTER TABLE fila ADD CONSTRAINT FK_8BF2F209C8B3A96B FOREIGN KEY (piano_id) REFERENCES piano (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_8BF2F209C8B3A96B ON fila (piano_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_fila_piano_lettera ON fila (piano_id, lettera)');
        $this->addSql('DROP INDEX IDX_16B834ACBD0F409C ON piano');
        $this->addSql('DROP INDEX uniq_piano_area_numero ON piano');
        $this->addSql('ALTER TABLE piano ADD nome VARCHAR(100) NOT NULL, DROP area_id, DROP numero, DROP etichetta');
        $this->addSql('CREATE UNIQUE INDEX uniq_piano_nome ON piano (nome)');
        $this->addSql('ALTER TABLE posizionamento DROP FOREIGN KEY FK_D316D772C8B3A96B');
        $this->addSql('DROP INDEX IDX_D316D772C8B3A96B ON posizionamento');
        $this->addSql('DROP INDEX uniq_posizionamento_materiale_piano ON posizionamento');
        $this->addSql('ALTER TABLE posizionamento DROP sezione, CHANGE piano_id ripiano_id INT NOT NULL');
        $this->addSql('ALTER TABLE posizionamento ADD CONSTRAINT FK_D316D7724C677C2E FOREIGN KEY (ripiano_id) REFERENCES ripiano (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D316D7724C677C2E ON posizionamento (ripiano_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_posizionamento_materiale_ripiano ON posizionamento (materiale_id, ripiano_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posizionamento DROP FOREIGN KEY FK_D316D7724C677C2E');
        $this->addSql('CREATE TABLE area (id INT AUTO_INCREMENT NOT NULL, fila_id INT NOT NULL, lato VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_D7943D681CA9EE3B (fila_id), UNIQUE INDEX uniq_area_fila_lato (fila_id, lato), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D681CA9EE3B FOREIGN KEY (fila_id) REFERENCES fila (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ripiano DROP FOREIGN KEY FK_19FA835F81E679D8');
        $this->addSql('ALTER TABLE sezione DROP FOREIGN KEY FK_68B494251CA9EE3B');
        $this->addSql('DROP TABLE ripiano');
        $this->addSql('DROP TABLE sezione');
        $this->addSql('DROP INDEX IDX_D316D7724C677C2E ON posizionamento');
        $this->addSql('DROP INDEX uniq_posizionamento_materiale_ripiano ON posizionamento');
        $this->addSql('ALTER TABLE posizionamento ADD sezione VARCHAR(10) DEFAULT NULL, CHANGE ripiano_id piano_id INT NOT NULL');
        $this->addSql('ALTER TABLE posizionamento ADD CONSTRAINT FK_D316D772C8B3A96B FOREIGN KEY (piano_id) REFERENCES piano (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D316D772C8B3A96B ON posizionamento (piano_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_posizionamento_materiale_piano ON posizionamento (materiale_id, piano_id)');
        $this->addSql('DROP INDEX uniq_piano_nome ON piano');
        $this->addSql('ALTER TABLE piano ADD area_id INT NOT NULL, ADD numero INT NOT NULL, ADD etichetta VARCHAR(100) DEFAULT NULL, DROP nome');
        $this->addSql('ALTER TABLE piano ADD CONSTRAINT FK_16B834ACBD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_16B834ACBD0F409C ON piano (area_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_piano_area_numero ON piano (area_id, numero)');
        $this->addSql('ALTER TABLE fila DROP FOREIGN KEY FK_8BF2F209C8B3A96B');
        $this->addSql('DROP INDEX IDX_8BF2F209C8B3A96B ON fila');
        $this->addSql('DROP INDEX uniq_fila_piano_lettera ON fila');
        $this->addSql('ALTER TABLE fila ADD codice VARCHAR(20) NOT NULL, ADD nome VARCHAR(100) NOT NULL, ADD tipo VARCHAR(20) NOT NULL, DROP piano_id, DROP lettera, DROP prima_fila, DROP ultima_fila');
        $this->addSql('CREATE UNIQUE INDEX uniq_fila_codice ON fila (codice)');
    }
}
