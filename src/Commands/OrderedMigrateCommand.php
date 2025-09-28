<?php

namespace MigrationOrderer\Commands;

use Illuminate\Console\Command;
use MigrationOrderer\Services\MigrationScanner;
use MigrationOrderer\Services\DependencyGraphBuilder;
use MigrationOrderer\Support\TopologicalSorter;
use MigrationOrderer\Services\MigrationRenamer;
use MigrationOrderer\Support\DependencyAnalyzer;
use MigrationOrderer\Support\PreviewTableBuilder;
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

            $analysis = DependencyAnalyzer::analyzeOrder($ordered, $metadataList);
            PreviewTableBuilder::display($this, $analysis);

            if ($this->option('preview') || (!$this->option('reorder') && $this->option('undo-last') === false)) {
                $this->comment('Safe mode: no action taken. Use --reorder to rename files, or --undo-last to restore.');
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

            $this->comment('No action chosen. Use --reorder or --undo-last.');
            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Migration Orderer Error: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }
            return self::FAILURE;
        }
    }

}
