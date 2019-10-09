<?php

namespace App\Command\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/***
 * Add user command
 */
class AddUserCommand extends Command
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

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pilea:user:add')
            ->setDescription('Add an user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setUsername($input->getArgument('username'));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $input->getArgument('password')));
        $user->setActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly created.');
    }
}
