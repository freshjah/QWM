<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SuspiciousActivityAlert;

class DetectSuspiciousActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:detect-suspicious-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for suspicious verification behavior';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = config('monitor.verification_failure_threshold', 5);
        $window = now()->subMinutes(10);

        $suspicious = Activity::where('description', 'Document verification attempted')
            ->whereJsonContains('properties->status', 'failure')
            ->where('created_at', '>=', $window)
            ->get()
            ->groupBy('properties.ip')
            ->filter(fn($group) => $group->count() > $threshold);

        if ($suspicious->isNotEmpty()) {
            Notification::route('mail', config('monitor.alert_recipient'))
                ->notify(new SuspiciousActivityAlert($suspicious));
        }

        $this->info("Checked for suspicious activity.");
    }
}
