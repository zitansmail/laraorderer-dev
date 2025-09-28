<?php

namespace MigrationOrderer\Support;

class MigrationNameFormatter
{
    public static function extractMigrationName(string $filename): string
    {
        $filename = str_replace('.php', '', $filename);

        $parts = explode('_', $filename);

        if (count($parts) > 4) {
            return implode('_', array_slice($parts, 4));
        }

        return $filename;
    }

    public static function extractMigrationNames(array $filePaths): array
    {
        return array_map(function ($filePath) {
            return self::extractMigrationName(basename($filePath));
        }, $filePaths);
    }

    public static function formatForDisplay(string $migrationName, int $maxLength = 30): string
    {
        if (strlen($migrationName) <= $maxLength) {
            return $migrationName;
        }

        return substr($migrationName, 0, $maxLength - 3) . '...';
    }
}