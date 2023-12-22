<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\JwtService;
use App\Services\SodiumCryptoService;
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
    public function __construct(
        private JwtService $jwtService,
        private SodiumCryptoService $sodiumCryptoService,
    ) {
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
            $io->warning("RSA keys seem to already exist, use '--force' to regenerate it.");
        } else {
            $io->text('Creating RSA keys...');
            $this->jwtService->generateRsaKey();

            $io->success('RSA key has been successfully generated.');
        }

        if (!$input->getOption('force') && $this->sodiumCryptoService->keyExists()) {
            $io->warning("Sodium Keypair seems to already exist, use '--force' to regenerate it.");
        } else {
            $io->text('Creating Sodium keypair...');
            $this->sodiumCryptoService->generateKeypair();

            $io->success('Sodium keypair has been successfully generated.');
        }

        return 0;
    }
}
