<?php

namespace Icap\PortfolioBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/10 04:26:41
 */
class Version20140410162639 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE icap__portfolio (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT NOT NULL, 
                name VARCHAR(128) NOT NULL, 
                slug VARCHAR(128) NOT NULL, 
                share_policy INT NOT NULL, 
                createdAt DATETIME NOT NULL, 
                updatedAt DATETIME NOT NULL, 
                deletedAt DATETIME DEFAULT NULL, 
                UNIQUE INDEX UNIQ_8B1895D989D9B62 (slug), 
                INDEX IDX_8B1895DA76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            CREATE TABLE icap__portfolio_shared_users (
                portfolio_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_8EACC994B96B5643 (portfolio_id), 
                INDEX IDX_8EACC994A76ED395 (user_id), 
                PRIMARY KEY(portfolio_id, user_id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio 
            ADD CONSTRAINT FK_8B1895DA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            ADD CONSTRAINT FK_8EACC994B96B5643 FOREIGN KEY (portfolio_id) 
            REFERENCES icap__portfolio (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            ADD CONSTRAINT FK_8EACC994A76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_shared_users 
            DROP FOREIGN KEY FK_8EACC994B96B5643
        ");
        $this->addSql("
            DROP TABLE icap__portfolio
        ");
        $this->addSql("
            DROP TABLE icap__portfolio_shared_users
        ");
    }
}