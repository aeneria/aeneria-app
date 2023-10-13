<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/***
 * Remove user command
 */
class ActivateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected SymfonyStyle $io;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:user:activate')
            ->setDescription('Activate an user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        if (!$user = $this->userRepository->findOneByUsername($input->getArgument('username'))) {
            $this->io->error("User can't be found.");

            return 1;
        }

        $user->setActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly desactivated.');

        return 0;
    }
}
