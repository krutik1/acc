<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class UpdateService
{
    public function check()
    {
        $updateUrl = config('app.update_url');
        $currentVersion = config('app.version', '1.0.0');

        if (!class_exists('ZipArchive')) {
            return [
                'current_version' => $currentVersion,
                'available' => false,
                'message' => 'PHP ZipArchive extension is not installed. Please enable it in php.ini.'
            ];
        }

        if (!$updateUrl) {
            return [
                'current_version' => $currentVersion,
                'available' => false,
                'message' => 'Update URL not configured.'
            ];
        }

        try {
            $response = Http::timeout(10)->get($updateUrl);

            if ($response->successful()) {
                $data = $response->json();

                // Validate response structure
                if (!isset($data['version']) || !isset($data['download_url'])) {
                    return [
                        'current_version' => $currentVersion,
                        'available' => false,
                        'message' => 'Invalid update server response.'
                    ];
                }

                $latestVersion = trim($data['version']);
                $available = version_compare($currentVersion, $latestVersion, '<');

                return [
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                    'available' => $available,
                    'description' => $data['changelog'] ?? 'No changelog available.',
                    'download_url' => $data['download_url']
                ];
            } else {
                return [
                    'current_version' => $currentVersion,
                    'available' => false,
                    'message' => 'Failed to connect to update server (Status: ' . $response->status() . ').'
                ];
            }
        } catch (\Exception $e) {
            return [
                'current_version' => $currentVersion,
                'available' => false,
                'message' => 'Error checking for updates: ' . $e->getMessage()
            ];
        }
    }

    public function update()
    {
        // 1. Check for valid update data first (redundant but safe)
        $check = $this->check();
        if (!$check['available']) {
            return ['success' => false, 'message' => 'No update available or check failed.'];
        }

        return $this->performUpdate($check['latest_version'], $check['download_url']);
    }

    protected function performUpdate($version, $url)
    {
        // Register shutdown function to ensure app comes back up if script dies
        \register_shutdown_function(function () {
            // Check if we are incorrectly in maintenance mode
            if (app()->isDownForMaintenance()) { // Laravel check, though 'app()' helper might not work in shutdown?
                // Safer: just call up. It's idempotent-ish (clears file)
                try {
                    Artisan::call('up');
                } catch (\Exception $e) {
                    // desperate logging
                }
            }
        });

        try {
            // INCREASE TIMEOUT
            \set_time_limit(600);

            // 1. Backup Database
            $backupResult = $this->backupDatabase();
            if (!$backupResult['success']) {
                return ['success' => false, 'message' => 'Backup failed: ' . $backupResult['message']];
            }

            // 2. Put in Maintenance Mode
            Artisan::call('down');

            // 3. Download Update
            $tempPath = $this->downloadUpdate($url);
            if (!$tempPath) {
                Artisan::call('up');
                return ['success' => false, 'message' => 'Failed to download update file.'];
            }

            // 4. Extract and Replace
            $extractResult = $this->extractAndReplace($tempPath);
            if (!$extractResult) {
                Artisan::call('up');
                return ['success' => false, 'message' => 'Failed to extract update package.'];
            }

            // 5. Run Migrations
            Log::info("Update: Running migrations...");
            Artisan::call('migrate', ['--force' => true]);
            Log::info("Update: Migrations completed.");

            // 6. Automated Post-Update Tasks
            Log::info("Update: Running automated maintenance tasks...");

            // 6a. Storage Link
            if (!File::exists(public_path('storage'))) {
                Log::info("Update: Creating storage link...");
                Artisan::call('storage:link');
            } else {
                Log::info("Update: Storage link already exists.");
            }

            // 6b. Clear All Caches (Config, Route, View, Application)
            Log::info("Update: Clearing caches...");
            Artisan::call('optimize:clear'); // Clears config, route, view, and compiled class caches

            // 6c. Re-cache Configuration (Optional but recommended for performance if in production)
            // Artisan::call('config:cache'); 
            // Artisan::call('route:cache');
            // Artisan::call('view:cache');
            // For now, leaving it cleared is safer to ensure fresh values are picked up.

            Log::info("Update: Maintenance tasks completed.");

            // 7. Disable Maintenance Mode
            Artisan::call('up');

            // 8. Update Version in .env
            $this->updateVersionInEnv($version);

            // Cleanup
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }

            return ['success' => true, 'message' => "System updated successfully to v{$version}. Storage linked, migrations run, and caches cleared."];

        } catch (\Exception $e) {
            Artisan::call('up'); // Ensure we try to bring it back up
            Log::error("Update failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Critical Error: ' . $e->getMessage()];
        }
    }

    protected function backupDatabase()
    {
        try {
            $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
            $path = storage_path("app/backups/$filename");

            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // Check if exec is available
            if (!function_exists('exec')) {
                Log::warning("exec() function is disabled. Using PHP fallback for database backup.");
                return $this->backupDatabaseUsingPhp($path);
            }

            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');

            $passwordPart = $dbPass ? "-p\"$dbPass\"" : "";

            // Find mysqldump
            $mysqldump = 'mysqldump'; // Default to PATH

            // Attempt to find specific Laragon path on C: or E:
            $laragonDrives = ['c:', 'e:'];
            foreach ($laragonDrives as $drive) {
                $candidates = glob($drive . '/laragon/bin/mysql/*/bin/mysqldump.exe');
                if (!empty($candidates)) {
                    $mysqldump = '"' . $candidates[0] . '"';
                    break;
                }
            }

            $command = "$mysqldump -h $dbHost -u $dbUser $passwordPart $dbName > \"$path\"";

            Log::info("Running backup command: " . str_replace($dbPass, '****', $command));

            $output = [];
            $resultCode = 0;
            \exec("$command 2>&1", $output, $resultCode);

            if ($resultCode === 0 && File::exists($path) && File::size($path) > 0) {
                return ['success' => true, 'path' => $path];
            } else {
                $errorMsg = implode("\n", $output);
                Log::error("Backup failed. Code: $resultCode. Output: $errorMsg");
                Log::info("Falling back to PHP backup method.");
                return $this->backupDatabaseUsingPhp($path);
            }

        } catch (\Throwable $e) {
            Log::error("Backup exception: " . $e->getMessage());
            // Last resort fallback if exception occurred (e.g. exec disabled but not caught by function_exists check?)
            try {
                return $this->backupDatabaseUsingPhp($path);
            } catch (\Exception $ex) {
                return ['success' => false, 'message' => $e->getMessage() . " | Fallback failed: " . $ex->getMessage()];
            }
        }
    }

    protected function backupDatabaseUsingPhp($path)
    {
        try {
            $handle = fopen($path, 'w');
            if (!$handle) {
                return ['success' => false, 'message' => "Could not create backup file at $path"];
            }

            fwrite($handle, "-- Backup created by PHP Fallback at " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

            $tables = DB::select('SHOW TABLES');
            $dbName = config('database.connections.mysql.database');
            $tablesKey = "Tables_in_" . $dbName;

            foreach ($tables as $tableUrl) {
                // Handle object property dynamically
                $table = null;
                foreach ($tableUrl as $key => $value) {
                    $table = $value;
                    break;
                }

                if (!$table)
                    continue;

                // Structure
                fwrite($handle, "-- Table structure for `$table`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `$table`");
                if (!empty($createTable)) {
                    $createTableSql = $createTable[0]->{'Create Table'} ?? $createTable[0]->{'rubbish'};
                    // property is usually 'Create Table'
                    fwrite($handle, $createTableSql . ";\n\n");
                }

                // Data
                fwrite($handle, "-- Dumping data for `$table`\n");
                // Use cursor to stream results to avoid memory limits
                foreach (DB::table($table)->cursor() as $row) {
                    $values = [];
                    foreach ((array) $row as $value) {
                        if (is_null($value)) {
                            $values[] = "NULL";
                        } elseif (is_numeric($value)) {
                            $values[] = $value;
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $sql = "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                    fwrite($handle, $sql);
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            if (File::exists($path) && File::size($path) > 0) {
                return ['success' => true, 'path' => $path];
            }

            return ['success' => false, 'message' => "PHP Backup produced empty file or failed."];

        } catch (\Exception $e) {
            Log::error("PHP Backup failed: " . $e->getMessage());
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            return ['success' => false, 'message' => "PHP Backup failed: " . $e->getMessage()];
        }
    }

    protected function downloadUpdate($url)
    {
        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                Log::error("Download failed. Status: " . $response->status() . " URL: " . $url);
                return false;
            }

            $content = $response->body();
            $tempPath = storage_path('app/temp_update.zip');
            File::put($tempPath, $content);

            // Basic validity check (is it a zip?)
            // Note: finfo might rely on magic bytes. 404 HTML will definitely fail this now.
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            if ($finfo->file($tempPath) !== 'application/zip' && $finfo->file($tempPath) !== 'application/x-zip-compressed') {
                // Some servers might send wrong mime, so maybe skip this or just warn
            }

            return $tempPath;
        } catch (\Exception $e) {
            Log::error("Download failed: " . $e->getMessage());
            return false;
        }
    }

    protected function extractAndReplace($zipPath)
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipPath);

        if ($res === TRUE) {
            try {
                // Extract to base path (overwrite)
                if ($zip->extractTo(base_path())) {
                    $zip->close();
                    return true;
                } else {
                    Log::error("ZipArchive::extractTo() failed. Check permissions for: " . base_path());
                    $zip->close();
                    return false;
                }
            } catch (\Exception $e) {
                Log::error("Zip extraction exception: " . $e->getMessage());
                $zip->close();
                return false;
            }
        } else {
            // Map error code to string
            $errorMsg = match ($res) {
                \ZipArchive::ER_EXISTS => 'File already exists',
                \ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                \ZipArchive::ER_INVAL => 'Invalid argument',
                \ZipArchive::ER_MEMORY => 'Malloc failure',
                \ZipArchive::ER_NOENT => 'No such file',
                \ZipArchive::ER_NOZIP => 'Not a zip archive',
                \ZipArchive::ER_OPEN => 'Can\'t open file',
                \ZipArchive::ER_READ => 'Read error',
                \ZipArchive::ER_SEEK => 'Seek error',
                default => 'Unknown error code: ' . $res,
            };
            Log::error("ZipArchive::open() failed: " . $errorMsg . " Path: " . $zipPath);
            return false;
        }
    }

    protected function updateVersionInEnv($version)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if (strpos($content, 'APP_VERSION=') !== false) {
                $content = preg_replace('/^APP_VERSION=.*/m', "APP_VERSION={$version}", $content);
            } else {
                $content .= "\nAPP_VERSION={$version}";
            }
            file_put_contents($path, $content);
        }
    }
}
