<?php

namespace App\Console\Commands;

use App\Services\ReminderScheduleService;
use Illuminate\Console\Command;

class SendDueRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send document expiry reminders to household members with duplicate prevention';

    /**
     * Execute the console command.
     */
    public function handle(ReminderScheduleService $service): int
    {
        $this->info('Starting document expiry reminder processing...');

        $results = $service->processAllDueReminders();

        $this->info("Reminders processed successfully:");
        $this->table(
            ['Sent', 'Failed', 'Skipped / Already Handled'],
            [[$results['sent'], $results['failed'], $results['skipped']]]
        );

        return Command::SUCCESS;
    }
}
