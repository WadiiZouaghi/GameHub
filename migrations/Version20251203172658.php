<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rename genre column to category in game table
 */
final class Version20251203172658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename genre column to category in game table';
    }

    public function up(Schema $schema): void
    {
        // Rename the column from genre to category
        $this->addSql('ALTER TABLE game CHANGE genre category VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Rename the column back from category to genre
        $this->addSql('ALTER TABLE game CHANGE category genre VARCHAR(100) NOT NULL');
    }
}
