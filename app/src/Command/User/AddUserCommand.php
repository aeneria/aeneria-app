<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/***
 * Add user command
 */
class AddUserCommand extends Command
{
    /** @var InputInterface */
    protected $defaultInput;

    /** @var SymfonyStyle */
    protected $io;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var UserPasswordHasherInterface */
    private $passwordHasher;
    /** @var UserRepository */
    private $userRepository;
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:user:add')
            ->setDescription('Add an user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        if ($this->userRepository->findOneByUsername($username)) {
            $this->io->error("User with this email already exists !");

            return 1;
        }

        if (0 !== \count($this->validator->validate($username, new Email()))) {
            $this->io->error(\sprintf('%s is not a valid email', $username));

            return 1;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $input->getArgument('password')));
        $user->setActive(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly created.');

        return 0;
    }
}
