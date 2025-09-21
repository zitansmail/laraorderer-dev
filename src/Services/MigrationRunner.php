<?php 

namespace MigrationOrderer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrationRunner
{
    public function runMigrations(array $orderedFiles)
    {
        if (!Schema::hasTable('migrations')) {
            DB::statement("CREATE TABLE migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255), batch INTEGER)");
        }

        foreach ($orderedFiles as $file) {
            $filename = basename($file);
            $migrationName = Str::before($filename, '.php');

            if (DB::table('migrations')->where('migration', $migrationName)->exists()) {
                echo "Skipping: $filename\n";
                continue;
            }

            echo "Running: $filename\n";
            $migration = require $file;
            $migration->up();

            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => DB::table('migrations')->max('batch') + 1,
            ]);
        }
    }
}
