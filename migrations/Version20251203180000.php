<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update coverImage paths from thunbnail to covers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE game SET cover_image = REPLACE(cover_image, 'uploads/thunbnail/', 'uploads/covers/') WHERE cover_image LIKE 'uploads/thunbnail/%'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE game SET cover_image = REPLACE(cover_image, 'uploads/covers/', 'uploads/thunbnail/') WHERE cover_image LIKE 'uploads/covers/%'");
    }
}
