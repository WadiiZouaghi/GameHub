<?php

namespace App\Command;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fix-game-images',
    description: 'Fix invalid cover image paths in games',
)]
class FixGameImagesCommand extends Command
{
    public function __construct(
        private GameRepository $gameRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $games = $this->gameRepository->findAll();
        $fixedCount = 0;

        foreach ($games as $game) {
            $coverImage = $game->getCoverImage();

            // Check if the cover image path is invalid (temporary file path)
            if ($coverImage && (strpos($coverImage, 'C:\\xampp\\tmp\\') === 0 || strpos($coverImage, '/tmp/') === 0)) {
                $game->setCoverImage(null);
                $fixedCount++;
                $io->writeln(sprintf('Fixed game "%s" (ID: %d)', $game->getTitle(), $game->getId()));
            }
        }

        if ($fixedCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Fixed %d games with invalid image paths.', $fixedCount));
        } else {
            $io->info('No games with invalid image paths found.');
        }

        return Command::SUCCESS;
    }
}
