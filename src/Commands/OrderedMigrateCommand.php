<?php

namespace MigrationOrderer\Commands;

use Illuminate\Console\Command;
use MigrationOrderer\Services\MigrationScanner;
use MigrationOrderer\Services\DependencyGraphBuilder;
use MigrationOrderer\Support\TopologicalSorter;
use MigrationOrderer\Services\MigrationRunner;
use MigrationOrderer\Services\MigrationRenamer;

class OrderedMigrateCommand extends Command
{
    protected $signature = 'migrate:ordered 
                            {--preview : Preview the order of migrations} 
                            {--json : Output as JSON} 
                            {--path= : Path to migration files} 
                            {--reorder : Reorder migration filenames based on dependencies}';

    protected $description = 'Run migrations in dependency-safe order or reorder migration files';

    public function handle()
    {
        $path = $this->option('path') ?? database_path('migrations');

        $scanner = new MigrationScanner();
        $graphBuilder = new DependencyGraphBuilder();
        $sorter = new TopologicalSorter();
        $runner = new MigrationRunner();
        $renamer = new MigrationRenamer();

        $metadataList = $scanner->scan($path);
        // dd($metadataList);

        $graph = $graphBuilder->build($metadataList);
        $ordered = $sorter->sort($graph);

        if ($this->option('preview') || $this->option('json')) {
            $data = array_map(fn($file) => $metadataList[$file] ?? null, $ordered);


            if ($this->option('json')) {
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            } else {
                foreach ($data as $meta) {
                    $this->line("📄 {$meta->filename}");
                    foreach ($meta->dependsOn as $d) {
                        $this->line("   ↳ depends on " . basename($d));
                    }
                    foreach ($meta->missing as $missingEntry) {
                        $this->warn("   ⚠️ Missing: $missingEntry");
                    }
                }
            }

            return;
        }

        if ($this->option('reorder')) {
            $renamer->reorder($ordered, $metadataList, $path, $this);
            return;
        }

        $runner->runMigrations($ordered);
        $this->info("✅ All migrations ran successfully.");
    }
}
