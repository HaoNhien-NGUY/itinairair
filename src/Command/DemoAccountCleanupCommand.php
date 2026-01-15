<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\TripRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:demo:account-cleanup',
    description: 'Remove demo accounts and demo trips older than 1 hour.',
)]
class DemoAccountCleanupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly TripRepository $tripRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $limitDate = new \DateTimeImmutable();

        $expiredDemoTrips = $this->tripRepository->findExpiredDemoTrips($limitDate);

        if (count($expiredDemoTrips) > 0) {
            foreach ($expiredDemoTrips as $trip) {
                $this->entityManager->remove($trip);
                $io->note(sprintf('removing demo trip %s', $trip->getId()));
            }

            $this->entityManager->flush();
        }

        $io->success(sprintf('%s demo trips removed.', count($expiredDemoTrips)));

        /** @var User[] $usersToDelete **/
        $usersToDelete = $this->userRepository->createQueryBuilder('u')
            ->andWhere('u.expiresAt < :now')
            ->andWhere('u.expiresAt IS NOT NULL')
            ->setParameter('now', $limitDate)
            ->getQuery()
            ->getResult();


        if (count($usersToDelete) > 0) {
            foreach ($usersToDelete as $user) {
                $this->entityManager->remove($user);
                $io->note(sprintf('removing demo user %s', $user->getId()));
            }

            $this->entityManager->flush();
        }

        $io->success(sprintf('%s demo user removed.', count($usersToDelete)));

        return Command::SUCCESS;
    }
}
