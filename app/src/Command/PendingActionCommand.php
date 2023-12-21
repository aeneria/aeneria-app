<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\PendingActionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Process expired pending actions
 */
class PendingActionCommand extends Command
{
    /** @var PendingActionService */
    private $pendingActionService;

    public function __construct(PendingActionService $pendingActionService)
    {
        $this->pendingActionService = $pendingActionService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('aeneria:pending-action:process-expired')
            ->setDescription('Process expired pending actions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pendingActionService->processAllExpiredPendingActions();

        return 0;
    }
}
