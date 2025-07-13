<?php

namespace MigrationOrderer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MigrationRenamer
{
    public function reorder(array $orderedFiles, array $metadataList, string $path, Command $console): void
    {
        $baseTimestamp = now()->startOfDay()->timestamp;

        foreach ($orderedFiles as $index => $oldPath) {
            $meta = $metadataList[$oldPath];
            $originalFilename = basename($meta->filename);

            // Extract name after timestamp
            $parts = explode('_', $originalFilename, 5);
            if (count($parts) < 5) {
                $console->warn("⚠️  Could not parse timestamp from: $originalFilename");
                continue;
            }

            $migrationSuffix = $parts[4]; // keep e.g., create_users_table.php
            $newTimestamp = date('Y_m_d_His', $baseTimestamp + $index);
            $newFilename = $newTimestamp . '_' . $migrationSuffix;

            // Skip if name is the same
            if ($originalFilename === $newFilename) {
                $console->line("✔️  Skipped (already ordered): $originalFilename");
                continue;
            }

            $newPath = $path . DIRECTORY_SEPARATOR . $newFilename;

            // Rename file
            rename($oldPath, $newPath);
            $console->line("✔️  Renamed: $originalFilename → $newFilename");

            // Update database migrations table
            $oldMigrationName = Str::before($originalFilename, '.php');
            $newMigrationName = Str::before($newFilename, '.php');

            DB::table('migrations')->where('migration', $oldMigrationName)->update([
                'migration' => $newMigrationName
            ]);
        }

        $console->info("✅ Reordering complete.");
    }
}
