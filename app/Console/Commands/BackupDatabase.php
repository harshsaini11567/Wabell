<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'app:backup-database';
    protected $description = 'Create daily database backup and remove old backups';
 
    public function handle()
    {
        $this->info('Starting database backup...');
 
        // Backup directory
        $backupPath = storage_path('database_backup');
 
        // Create directory if not exists
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }
 
        // File name: d-m-Y_database_backup.sql
        $fileName = Carbon::now()->format('d-m-Y') . '_database_backup.sql';
        $filePath = $backupPath . DIRECTORY_SEPARATOR . $fileName;
 
        // Remove old backups (keep only today)
        $files = File::files($backupPath);        
 
        // Database credentials
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
 
        // Full backup file path
        $filePath = str_replace('\\', '/', $filePath); // Windows fix
 
        $command = "\"mysqldump\" "
            . "--no-defaults "
            . "-h{$dbHost} "
            . "-P{$dbPort} "
            . "-u{$dbUser} "
            . ($dbPass ? "-p{$dbPass} " : "")
            . "{$dbName} > \"{$filePath}\"";
 
        exec($command, $output, $result);
 
        if ($result !== 0) {
            $this->error('Database backup failed!');
            return Command::FAILURE;
        }
 
        foreach ($files as $file) {
            if ($file->getFilename() !== $fileName) {
                File::delete($file->getPathname());
            }
        }
 
        $this->info("Database backup created: {$fileName}");
        $this->info('Old backups removed successfully.');
 
        return Command::SUCCESS;
    }
}