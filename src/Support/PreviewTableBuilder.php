<?php

namespace MigrationOrderer\Support;

use Illuminate\Console\Command;

class PreviewTableBuilder
{
    public static function display(Command $command, array $analysis): void
    {
        $rows = [];

        foreach ($analysis as $item) {
            $migrationName = MigrationNameFormatter::extractMigrationName(basename($item['file']));
            $status = $item['is_correct'] ? '<info>OK</info>' : '<comment>NEEDS REORDER</comment>';

            $dependencyNames = MigrationNameFormatter::extractMigrationNames($item['dependencies']);
            $dependsOnStr = empty($dependencyNames) ? '-' : implode(', ', $dependencyNames);

            $issue = '';
            if ($item['violation']) {
                $violationName = MigrationNameFormatter::extractMigrationName(basename($item['violation']));
                $issue = "<error>Depends on: {$violationName}</error>";
            }

            $rows[] = [
                $item['computed_position'],
                $migrationName,
                $item['current_position'],
                $status,
                $dependsOnStr,
                $issue
            ];
        }

        $command->table([
            '# (Computed)',
            'Migration',
            'Current Pos',
            'Status',
            'Dependencies',
            'Issue'
        ], $rows);

        $needsReorder = DependencyAnalyzer::countReorderNeeded($analysis);
        if ($needsReorder > 0) {
            $command->warn("{$needsReorder} migration(s) need reordering for dependency safety.");
        } else {
            $command->info("All migrations are in dependency-safe order.");
        }
    }
}