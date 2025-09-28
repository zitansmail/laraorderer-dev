<?php

namespace MigrationOrderer\Services;

use MigrationOrderer\Data\MigrationMetadata;
use MigrationOrderer\Exceptions\DirectoryNotFoundException;
use Illuminate\Support\Str;

class MigrationScanner
{
    protected array $foreignKeyPatterns = [
        // Standard foreign key constraints
        "/->foreign\([^)]+\)->references\([^)]+\)->on\(['\"](\w+)['\"]/",
        // foreignId with implicit constrained
        "/->foreignId\(['\"](\w+)_id['\"]\)->constrained\(\)/",
        // foreignId with explicit table
        "/->foreignId\([^)]+\)->constrained\(['\"](\w+)['\"]/",
        // foreignIdFor helper
        "/->foreignIdFor\([^,]+,\s*['\"](\w+)['\"]/",
        // Legacy foreign keys
        "/->unsignedBigInteger\(['\"](\w+)_id['\"]\)/",
    ];

    protected array $polymorphicPatterns = [
        // Standard morphs
        "/->morphs\(['\"](\w+)['\"]\)/",
        // UUID morphs
        "/->ulidMorphs\(['\"](\w+)['\"]\)/",
        "/->uuidMorphs\(['\"](\w+)['\"]\)/",
    ];

    public function scan(string $path): array
    {
        if (!is_dir($path)) {
            throw new DirectoryNotFoundException("Directory not found: {$path}");
        }

        $files = glob($path . '/*.php');
        $tableToFile = [];
        $metadataList = [];

        // First pass: map tables to their creation files
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $tablesCreated = $this->extractCreatedTables($content);

            foreach ($tablesCreated as $table) {
                $tableToFile[$table] = $file;
            }
        }

        // Second pass: analyze dependencies
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $dependsOn = [];
            $missing = [];
            $tablesCreated = $this->extractCreatedTables($content);

            // Extract foreign key dependencies
            $referencedTables = $this->extractForeignKeyTables($content);

            foreach ($referencedTables as $table) {
                if (isset($tableToFile[$table]) && $tableToFile[$table] !== $file) {
                    $dependsOn[] = $tableToFile[$table];
                } else if (!isset($tableToFile[$table])) {
                    $missing[] = $table;
                }
            }

            $metadataList[$file] = new MigrationMetadata(
                basename($file),
                array_unique($dependsOn),
                $missing,
                $tablesCreated
            );
        }

        return $metadataList;
    }

    protected function extractCreatedTables(string $content): array
    {
        $tables = [];

        // Schema::create patterns
        if (preg_match_all("/Schema::create\(['\"](\w+)['\"]/", $content, $matches)) {
            $tables = array_merge($tables, $matches[1]);
        }

        return array_unique($tables);
    }

    protected function extractForeignKeyTables(string $content): array
    {
        $tables = [];

        foreach ($this->foreignKeyPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Convert singular column names to plural table names for foreignId patterns
                    if (str_contains($pattern, 'foreignId') && str_contains($pattern, 'constrained\\(\\)')) {
                        $tables[] = Str::plural($match);
                    } else {
                        $tables[] = $match;
                    }
                }
            }
        }

        // Handle polymorphic relationships (these don't create specific table dependencies)
        foreach ($this->polymorphicPatterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            // Note: Polymorphic relationships don't create hard dependencies
            // but could be logged for informational purposes
        }

        return array_unique($tables);
    }
}
