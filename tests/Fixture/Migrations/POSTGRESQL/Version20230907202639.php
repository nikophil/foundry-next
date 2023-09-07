<?php

declare(strict_types=1);

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Migrations\POSTGRESQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230907202639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE StandardEntity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE StandardRelationEntity_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE StandardEntity (id INT NOT NULL, relation_id INT DEFAULT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FB1733C13256915B ON StandardEntity (relation_id)');
        $this->addSql('CREATE TABLE StandardRelationEntity (id INT NOT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE StandardEntity ADD CONSTRAINT FK_FB1733C13256915B FOREIGN KEY (relation_id) REFERENCES StandardRelationEntity (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE StandardEntity_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE StandardRelationEntity_id_seq CASCADE');
        $this->addSql('ALTER TABLE StandardEntity DROP CONSTRAINT FK_FB1733C13256915B');
        $this->addSql('DROP TABLE StandardEntity');
        $this->addSql('DROP TABLE StandardRelationEntity');
    }
}
