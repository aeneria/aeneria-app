<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\JwtService;
use App\Services\SodiumCryptoService;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Inspired by Wallabag install command.
 */
class InstallCommand extends Command
{
    protected InputInterface $defaultInput;
    protected SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private JwtService $jwtService,
        private SodiumCryptoService $sodiumCryptoService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:install')
            ->setDescription('aeneria installer.')
            ->addArgument('user', InputArgument::OPTIONAL, 'Linux user who will run aeneria cron')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset current database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaultInput = $input;

        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('aeneria installer');

        $this
            ->checkRequirements()
            ->setupDatabase()
            ->setupMigration()
            ->clearCache()
            ->generateRsaKey()
        ;

        $this->io->success('aeneria has been successfully installed.');

        return 0;
    }

    protected function checkRequirements()
    {
        $this->io->section('Step 1 of 4: Checking system requirements.');

        $doctrineManager = $this->entityManager;

        $rows = [];

        // testing if database driver exists
        $fulfilled = true;
        $label = '<comment>PDO Driver (%s)</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        // testing if connection to the database can be etablished
        $label = '<comment>Database connection</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        try {
            $conn = $this->entityManager->getConnection();
            $conn->connect();
        } catch (\Exception $e) {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'Can\'t connect to the database: ' . $e->getMessage();
        }

        $rows[] = [$label, $status, $help];

        // check PostgreSQL version
        $label = '<comment>Database version</comment>';
        $status = '<info>OK!</info>';
        $help = '';

        // testing if connection and ensure sgbd is PostgreSQL > 9.1
        if ($fulfilled && !$conn->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $fulfilled = false;
            $status = '<error>ERROR!</error>';
            $help = 'Only PostgreSQL is supported';
        }

        if ($fulfilled) {
            // return version should be like "PostgreSQL 9.5.4 on x86_64-apple-darwin15.6.0, compiled by Apple LLVM version 8.0.0 (clang-800.0.38), 64-bit"
            $version = (string) $doctrineManager->getConnection()->executeQuery('SELECT version();')->fetchFirstColumn()[0];

            \preg_match('/PostgreSQL ([0-9\.]+)/i', $version, $matches);

            if (isset($matches[1]) & \version_compare($matches[1], '9.6.0', '<')) {
                $fulfilled = false;
                $status = '<error>ERROR!</error>';
                $help = 'PostgreSQL should be greater than 9.1 (actual version: ' . $matches[1] . ')';
            }
        }

        $rows[] = [$label, $status, $help];

        $this->io->table(['Checked', 'Status', 'Recommendation'], $rows);

        if (!$fulfilled) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $this->io->text('<info>Success! Seems that your system can run aeneria properly.</info>');

        return $this;
    }

    protected function setupDatabase()
    {
        $this->io->section('Step 2 of 4: Setting up database.');

        // user want to reset everything? Don't care about what is already here
        if (true === $this->defaultInput->getOption('reset')) {
            $this->io->text('Dropping database, creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true])
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;

            $this->io->newLine();

            return $this;
        }

        if (!$this->isDatabasePresent()) {
            $this->io->text('Creating database and schema, clearing the cache');

            $this
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;

            $this->io->newLine();

            return $this;
        }

        if ($this->io->confirm('It appears that your database already exists. Would you like to reset it?', false)) {
            $this->io->text('Dropping database, creating database and schema...');

            $this
                ->runCommand('doctrine:database:drop', ['--force' => true])
                ->runCommand('doctrine:database:create')
                ->runCommand('doctrine:schema:create')
            ;
        } elseif ($this->isSchemaPresent()) {
            if ($this->io->confirm('Seems like your database contains schema. Do you want to reset it?', false)) {
                $this->io->text('Dropping schema and creating schema...');

                $this
                    ->runCommand('doctrine:schema:drop', ['--force' => true])
                    ->runCommand('doctrine:schema:create')
                ;
            }
        } else {
            $this->io->text('Creating schema...');
            $this->runCommand('doctrine:schema:create');
        }

        $this->io->newLine();
        $this->io->text('<info>Database successfully setup.</info>');

        return $this;
    }

    protected function setupMigration()
    {
        $this->io->section('Step 3 of 4: Setting up migration mechanism.');
        $this->runCommand('doctrine:migrations:sync-metadata-storage', ['--no-interaction' => true]);
        $this->runCommand('doctrine:migrations:version', ['--add' => true, '--all' => true, '--no-interaction' => true]);
        $this->io->text('<info>Migration mechanism successfully setup.</info>');

        return $this;
    }

    protected function clearCache()
    {
        $this->io->text('Clearing the cache...');
        $this->runCommand('cache:clear');

        return $this;
    }

    protected function generateKeys()
    {
        $this->io->section('Step 4 of 4: Creating RSA key.');
        $this->io->text('Creating key...');
        $this->jwtService->generateRsaKey();
        $this->sodiumCryptoService->generateKeypair();
    }

    /**
     * Run a command.
     *
     * @param string $command
     * @param array  $parameters Parameters to this command (usually 'force' => true)
     */
    protected function runCommand($command, $parameters = [])
    {
        $parameters = \array_merge(
            ['command' => $command],
            $parameters,
            [
                '--no-debug' => true,
                '--env' => $this->defaultInput->getOption('env') ?: 'dev',
            ]
        );

        if ($this->defaultInput->getOption('no-interaction')) {
            $parameters = \array_merge($parameters, ['--no-interaction' => true]);
        }

        $this->getApplication()->setAutoExit(false);

        $output = new BufferedOutput();
        $exitCode = $this->getApplication()->run(new ArrayInput($parameters), $output);

        // PDO does not always close the connection after Doctrine commands.
        // See https://github.com/symfony/symfony/issues/11750.
        $this->entityManager->getConnection()->close();

        if (0 !== $exitCode) {
            $this->getApplication()->setAutoExit(true);

            throw new \RuntimeException(
                'The command "' . $command . "\" generates some errors: \n\n"
                . $output->fetch()
            );
        }

        return $this;
    }

    /**
     * Check if the database already exists.
     *
     * @return bool
     */
    private function isDatabasePresent()
    {
        $connection = $this->entityManager->getConnection();
        $databaseName = $connection->getDatabase();

        try {
            $schemaManager = $connection->getSchemaManager();
        } catch (\Exception $exception) {
            // mysql & sqlite
            if (false !== \strpos($exception->getMessage(), \sprintf("Unknown database '%s'", $databaseName))) {
                return false;
            }

            // pgsql
            if (false !== \strpos($exception->getMessage(), \sprintf('database "%s" does not exist', $databaseName))) {
                return false;
            }

            throw $exception;
        }

        // custom code for sqlite, since `getListDatabasesSQL` doesn't work for sqlite
        if ('sqlite' === $schemaManager->getDatabasePlatform()->getName()) {
            return true;
        }

        try {
            return \in_array($databaseName, $schemaManager->listDatabases(), true);
        } catch (\Doctrine\DBAL\Exception\DriverException $e) {
            // it means we weren't able to get database list, assume the database doesn't exist

            return false;
        }
    }

    /**
     * Check if the schema is already created.
     * If we found at least oen table, it means the schema exists.
     *
     * @return bool
     */
    private function isSchemaPresent()
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();

        return \count($schemaManager->listTableNames()) > 0 ? true : false;
    }
}
