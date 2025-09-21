<?php

namespace MigrationOrderer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

use MigrationOrderer\Services\MigrationHandler;

class MigrationRenamer
{
    protected MigrationHandler $state;

    public function __construct(?MigrationHandler $state = null)
    {
        $this->state = $state ?: new MigrationHandler();
    }

    public function reorder(array $orderedFiles, array $metadataList, string $path, Command $console): void
    {
        $baseTimestamp = now()->startOfDay()->timestamp;
        $manifest = [];

        foreach ($orderedFiles as $index => $oldPath) {
            $meta = $metadataList[$oldPath];
            $originalFilename = basename($meta->filename);

            $parts = explode('_', $originalFilename, 5);
            if (count($parts) < 5) {
                $console->warn("Could not parse timestamp from: $originalFilename");
                continue;
            }

            $migrationSuffix = $parts[4];
            $newTimestamp = date('Y_m_d_His', $baseTimestamp + $index);
            $newFilename = $newTimestamp . '_' . $migrationSuffix;

            if ($originalFilename === $newFilename) {
                $console->line("Skipped (already ordered): $originalFilename");
                continue;
            }

            $newPath = $path . DIRECTORY_SEPARATOR . $newFilename;
            rename($oldPath, $newPath);

            $console->line("Renamed: $originalFilename → $newFilename");

            $manifest[] = [
                'from' => $oldPath,
                'to'   => $newPath,
            ];

            $oldMigrationName = Str::before($originalFilename, '.php');
            $newMigrationName = Str::before($newFilename, '.php');
            DB::table('migrations')->where('migration', $oldMigrationName)->update([
                'migration' => $newMigrationName,
            ]);
        }

        $this->state->clearManifest();
        $this->state->saveManifest($manifest);
        $console->info("Reordering complete. Manifest stored in DB.");
    }

    public function undoLast(Command $console): void
    {
        $entries = $this->state->readManifest();
        if (empty($entries)) {
            $console->warn('No previous reorder manifest found.');
            return;
        }

        foreach (array_reverse($entries) as $entry) {
            $from = $entry->from;
            $to   = $entry->to;

            if (file_exists($to)) {
                rename($to, $from);
                $console->line("Restored: " . basename($to) . " → " . basename($from));
            } else {
                $console->warn("Missing file to restore: $to");
            }
        }

        $this->state->clearManifest();
        $console->info('Undo complete. DB manifest cleared.');
    }
}
