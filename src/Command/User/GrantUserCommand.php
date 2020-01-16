<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
/***
 * Grant user as admin command
 */
class GrantUserCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $defaultInput;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pilea:user:grant')
            ->setDescription('Grant user admin role.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($input->getArgument('username'));
        $user->setRoles([User::ROLE_ADMIN]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly granted as admin.');

        return 0;
    }
}
