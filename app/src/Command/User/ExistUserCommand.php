<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/***
 * Edit user command
 */
class ExistUserCommand extends Command
{
    protected SymfonyStyle $io;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:user:exist')
            ->setDescription('Does an user exist ?')
            ->addArgument('username', InputArgument::REQUIRED, 'Email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(
            (string) (!!$this->userRepository->findOneByUsername($input->getArgument('username')) ? 1 : 0)
        );

        return 0;
    }
}
