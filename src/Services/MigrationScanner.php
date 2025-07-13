<?php 

namespace MigrationOrderer\Services;

use MigrationOrderer\Data\MigrationMetadata;
use Illuminate\Support\Str;

class MigrationScanner
{
    public function scan(string $path): array
    {
        $files = glob($path . '/*.php');
        $tableToFile = [];
        $metadataList = [];

        // 1st pass: Map tables created to filenames
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match("/Schema::create\(['\"](\w+)['\"]/", $content, $match)) {
                $tableToFile[$match[1]] = $file;
            }
        }

        // 2nd pass: Extract dependencies
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $dependsOn = [];
            $missing = [];

            preg_match_all("/->foreign\([^)]+\)->references\([^)]+\)->on\(['\"](\w+)['\"]/", $content, $m1);
            preg_match_all("/->constrained(?:\(['\"]?(\w*)['\"]?\))?/", $content, $m2);
            preg_match_all("/->foreignId\(['\"](\w+)_id['\"]\)->constrained\(\)/", $content, $m3);

            $tables = array_merge($m1[1] ?? [], $m2[1] ?? [], array_map(fn($c) => Str::plural($c), $m3[1] ?? []));

            foreach ($tables as $tbl) {
                if (isset($tableToFile[$tbl])) {
                    $dependsOn[] = $tableToFile[$tbl];
                } else {
                    $missing[] = $tbl . " (table)";
                }
            }

            $metadataList[$file] = new MigrationMetadata(basename($file), $dependsOn, $missing);
        }

        return $metadataList;
    }
}
