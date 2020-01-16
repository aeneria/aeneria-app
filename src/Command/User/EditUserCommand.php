<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/***
 * Edit user command
 */
class EditUserCommand extends Command
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
    private $passwordEncoder;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pilea:user:edit')
            ->setDescription('Edit an user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Old username')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'New username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'New passaword')
            ->addOption('active', 'a', InputOption::VALUE_OPTIONAL, 'New active parameter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        if ( !$user = $this->userRepository->findOneByUsername($input->getArgument('username'))) {
            $this->io->error("User can't be found.");
            return;
        }

        if ($username = $input->getOption('username')) {
            $user->setUsername($username);
        }
        if ($password = $input->getOption('password')) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        }
        if ($active = $input->getOption('active')) {
            $user->setActive($active);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly updated.');

        return 0;
    }
}
