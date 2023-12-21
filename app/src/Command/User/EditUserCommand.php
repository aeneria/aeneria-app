<?php

declare(strict_types=1);

namespace App\Command\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/***
 * Edit user command
 */
class EditUserCommand extends Command
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
            ->setName('aeneria:user:edit')
            ->setDescription('Edit an user.')
            ->addArgument('username', InputArgument::REQUIRED, 'Current email')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'New email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'New passaword')
            ->addOption('active', 'a', InputOption::VALUE_OPTIONAL, 'New active parameter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        if (!$user = $this->userRepository->findOneByUsername($input->getArgument('username'))) {
            $this->io->error("User can't be found.");

            return 1;
        }

        if ($username = $input->getOption('username')) {
            if (0 !== \count($this->validator->validate($username, new Email()))) {
                $this->io->error(\sprintf('%s is not a valid email', $username));

                return 1;
            }

            $user->setUsername($username);
        }
        if ($password = $input->getOption('password')) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }
        if (!\is_null($active = $input->getOption('active'))) {
            $user->setActive($active);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success('User has been correctly updated.');

        return 0;
    }
}
