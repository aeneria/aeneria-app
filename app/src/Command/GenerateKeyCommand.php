<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\JwtService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/***
 * Generate keys
 */
class GenerateKeyCommand extends Command
{
    /** @var JwtService */
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:generate-key')
            ->setDescription('Generate RSA key.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force keys to be regenerate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force') && $this->jwtService->keyExists()) {
            $io->warning("Key seems to already exists, use '--force' to regenerate it.");

            return 0;
        }

        $io->text('Creating key...');
        $this->jwtService->generateRsaKey();

        $io->success('RSA key has been successfully generated.');

        return 0;
    }
}
