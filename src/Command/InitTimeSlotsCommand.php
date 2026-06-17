<?php

namespace App\Command;

use App\Service\CalendarService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:init-time-slots',
    description: 'Initialize business hours time slots',
)]
class InitTimeSlotsCommand extends Command
{
    public function __construct(private CalendarService $calendarService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->calendarService->initializeTimeSlots();

        $output->writeln('<info>Time slots initialized successfully!</info>');

        return Command::SUCCESS;
    }
}
