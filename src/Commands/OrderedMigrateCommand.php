<?php

namespace MigrationOrderer\Commands;

use Illuminate\Console\Command;
use MigrationOrderer\Services\MigrationScanner;
use MigrationOrderer\Services\DependencyGraphBuilder;
use MigrationOrderer\Support\TopologicalSorter;
use MigrationOrderer\Services\MigrationRunner;
use MigrationOrderer\Services\MigrationRenamer;
use Throwable;

class OrderedMigrateCommand extends Command
{
    protected $signature = 'migrate:ordered
        {--path=database/migrations : Path to migrations directory}
        {--preview : Show computed order without executing}
        {--reorder : Rename migration files to enforce order}
        {--undo-last : Undo the last reorder using the saved manifest}
        {--force : Bypass confirmation in non-interactive/CI environments}
    ';

    protected $description = 'Compute dependency-safe order. Use --run to execute, --reorder to rename, --undo-last to restore. Safe by default.';

    public function handle(
        MigrationScanner $scanner,
        DependencyGraphBuilder $graphBuilder,
        TopologicalSorter $sorter,
        MigrationRunner $runner,
        MigrationRenamer $renamer
    ) {
        $path = base_path($this->option('path'));

        try {
            $this->info('Scanning migrations...');
            $metadataList = $scanner->scan($path);

            $this->info('Building dependency graph...');
            $graph = $graphBuilder->build($metadataList);

            $this->info('Computing topological order...');
            $ordered = $sorter->sort($graph);

            $this->table(['#', 'File'], array_map(function ($f, $i) {
                return [$i + 1, $f];
            }, $ordered, array_keys($ordered)));

            // Non-destructive defaults:
            if ($this->option('preview') || (!$this->option('reorder') && $this->option('undo-last') === false)) {
                $this->comment('Safe mode: no action taken. Use --run to execute, --reorder to rename, or --undo-last to restore.');
                return self::SUCCESS;
            }

            if ($this->option('undo-last')) {
                $renamer->undoLast($this);
                return self::SUCCESS;
            }

            if ($this->option('reorder')) {
                if (!$this->option('force') && !$this->confirm('This will rename files. Continue?')) {
                    $this->warn('Aborted.');
                    return self::SUCCESS;
                }
                $renamer->reorder($ordered, $metadataList, $path, $this);
                return self::SUCCESS;
            }

            // Fallback if multiple flags omitted
            $this->comment('No action chosen. Use --run, --reorder, or --undo-last.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Unexpected Error' . PHP_EOL . $e->getMessage());
            return self::FAILURE;
        }
    }
}
