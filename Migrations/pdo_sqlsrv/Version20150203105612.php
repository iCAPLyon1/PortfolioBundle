<?php

namespace Icap\PortfolioBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2015/02/03 10:56:15
 */
class Version20150203105612 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE UNIQUE INDEX portfolio_slug_unique_idx ON icap__portfolio_widget_title (slug) 
            WHERE slug IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD uri NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            ADD uriLabel NVARCHAR(255)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            DROP COLUMN uri
        ");
        $this->addSql("
            ALTER TABLE icap__portfolio_widget_formations_resource 
            DROP COLUMN uriLabel
        ");
        $this->addSql("
            IF EXISTS (
                SELECT * 
                FROM sysobjects 
                WHERE name = 'portfolio_slug_unique_idx'
            ) 
            ALTER TABLE icap__portfolio_widget_title 
            DROP CONSTRAINT portfolio_slug_unique_idx ELSE 
            DROP INDEX portfolio_slug_unique_idx ON icap__portfolio_widget_title
        ");
    }
}