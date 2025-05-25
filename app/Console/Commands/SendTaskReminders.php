<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskDueReminders;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for tasks that are due soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Dispatching task reminder job...');
        
        SendTaskDueReminders::dispatch();
        
        $this->info('Task reminder job has been dispatched.');
    }
}
