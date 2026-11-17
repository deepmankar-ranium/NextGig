<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;

/**
 * Wipes the database and reloads the demo data. Refuses to run unless DEMO_MODE=true (or in
 * local/testing), so it can never wipe a real production database by accident.
 */
class ResetDemo extends Command
{
    protected $signature = 'demo:reset {--force : Skip the confirmation prompt}';

    protected $description = 'Wipe the database and reload demo data (DEMO_MODE only)';

    public function handle(): int
    {
        if (! config('app.demo_mode') && ! app()->environment('local', 'testing')) {
            $this->error('demo:reset only runs when DEMO_MODE=true (or in local/testing).');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This deletes ALL data and reloads the demo. Continue?')) {
            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        $this->info('Demo data reloaded. Log in as employer@nextgig.test or seeker@nextgig.test (password: password).');

        return self::SUCCESS;
    }
}
