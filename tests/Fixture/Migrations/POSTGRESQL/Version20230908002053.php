<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Tests\Fixture\Migrations\POSTGRESQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908002053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE Entity1_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Entity2_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Entity3_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE Entity4_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE Entity1 (id INT NOT NULL, relation_id INT DEFAULT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F0617053256915B ON Entity1 (relation_id)');
        $this->addSql('CREATE TABLE Entity2 (id INT NOT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE Entity3 (id INT NOT NULL, relation_id INT DEFAULT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E10876293256915B ON Entity3 (relation_id)');
        $this->addSql('CREATE TABLE Entity4 (id INT NOT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE Entity1 ADD CONSTRAINT FK_F0617053256915B FOREIGN KEY (relation_id) REFERENCES Entity2 (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE Entity3 ADD CONSTRAINT FK_E10876293256915B FOREIGN KEY (relation_id) REFERENCES Entity4 (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE Entity1_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Entity2_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Entity3_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE Entity4_id_seq CASCADE');
        $this->addSql('ALTER TABLE Entity1 DROP CONSTRAINT FK_F0617053256915B');
        $this->addSql('ALTER TABLE Entity3 DROP CONSTRAINT FK_E10876293256915B');
        $this->addSql('DROP TABLE Entity1');
        $this->addSql('DROP TABLE Entity2');
        $this->addSql('DROP TABLE Entity3');
        $this->addSql('DROP TABLE Entity4');
    }
}
