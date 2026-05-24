<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Domains\Core\User\Models\User;

class UpdateUserAvailability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-availability';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark users available if till_offline date is today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $users = User::where('is_available', 0)
            ->whereNotNull('till_offline') // Only users who actually set a date
            ->whereDate('till_offline', '<=', $today) // In case till_offline is before or same as today
            ->get();

        foreach ($users as $user) {
            $user->update([
                'is_available' => 1,
                'till_offline' => null,
            ]);
        }

        $this->info("Availability updated for {$users->count()} users.");
        return 0;
    }
}
