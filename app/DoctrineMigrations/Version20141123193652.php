<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Switch click.log column type from PHP serialized to JSON.
 */
class Version20141123193652 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ip_ban CHANGE ipv4 ipv4 BINARY(4) NULL, CHANGE ipv6 ipv6 BINARY(16) NULL');
        $this->addSql('ALTER TABLE click CHANGE log log LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function postUp(Schema $schema)
    {
        $fetchStmt  = $this->connection->prepare("SELECT id, log FROM click");
        $updateStmt = $this->connection->prepare("UPDATE click SET log = :log WHERE id = :id");

        $fetchStmt->execute();

        $this->connection->beginTransaction();
        while ($row = $fetchStmt->fetch()) {
            $updateStmt->execute([
                'id'  => $row['id'],
                'log' => json_encode(@unserialize($row['log'])),
            ]);
        }
        $this->connection->commit();
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE click CHANGE log log LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE ip_ban CHANGE ipv4 ipv4 TINYBLOB DEFAULT NULL, CHANGE ipv6 ipv6 TINYBLOB DEFAULT NULL');
    }

    public function postDown(Schema $schema)
    {
        $fetchStmt  = $this->connection->prepare("SELECT id, log FROM click");
        $updateStmt = $this->connection->prepare("UPDATE click SET log = :log WHERE id = :id");

        $fetchStmt->execute();

        $this->connection->beginTransaction();
        while ($row = $fetchStmt->fetch()) {
            $updateStmt->execute([
                'id'  => $row['id'],
                'log' => serialize(json_decode($row['log'], true)),
            ]);
        }
        $this->connection->commit();
    }
}
