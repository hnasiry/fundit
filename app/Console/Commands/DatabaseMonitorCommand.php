<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class DatabaseMonitorCommand extends Command
{
    protected $signature = 'db:monitor';
    protected $description = 'Check if the database is ready';

    public function handle()
    {
        try {
            DB::connection()->getPdo();
            return 0; // Success
        } catch (PDOException $e) {
            $this->error("Could not connect to the database. Please check your configuration. Error: " . $e->getMessage());
            return 1; // Error
        }
    }
}
