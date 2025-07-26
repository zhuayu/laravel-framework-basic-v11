<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\TestNotification;
use App\Models\User;
use Notification;

class NotificationTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereIn('id', [1])->get();
        Notification::send($users, new TestNotification());
    }
}
