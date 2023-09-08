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

namespace Zenstruck\Foundry\Tests\Fixture\Migrations\MYSQL;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230908002051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Entity1 (id INT AUTO_INCREMENT NOT NULL, relation_id INT DEFAULT NULL, prop1 VARCHAR(255) NOT NULL, INDEX IDX_F0617053256915B (relation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Entity2 (id INT AUTO_INCREMENT NOT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Entity3 (id INT AUTO_INCREMENT NOT NULL, relation_id INT DEFAULT NULL, prop1 VARCHAR(255) NOT NULL, INDEX IDX_E10876293256915B (relation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Entity4 (id INT AUTO_INCREMENT NOT NULL, prop1 VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Entity1 ADD CONSTRAINT FK_F0617053256915B FOREIGN KEY (relation_id) REFERENCES Entity2 (id)');
        $this->addSql('ALTER TABLE Entity3 ADD CONSTRAINT FK_E10876293256915B FOREIGN KEY (relation_id) REFERENCES Entity4 (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Entity1 DROP FOREIGN KEY FK_F0617053256915B');
        $this->addSql('ALTER TABLE Entity3 DROP FOREIGN KEY FK_E10876293256915B');
        $this->addSql('DROP TABLE Entity1');
        $this->addSql('DROP TABLE Entity2');
        $this->addSql('DROP TABLE Entity3');
        $this->addSql('DROP TABLE Entity4');
    }
}
