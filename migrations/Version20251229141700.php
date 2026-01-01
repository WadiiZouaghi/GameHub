<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251229141700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CASCADE delete constraints for game relationships';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6E48FD905');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BE48FD905');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7E48FD905');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6E48FD905');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BE48FD905');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }
}
