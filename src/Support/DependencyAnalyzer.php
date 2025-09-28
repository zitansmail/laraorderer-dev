<?php

namespace MigrationOrderer\Support;

class DependencyAnalyzer
{
    public static function findFirstViolation(string $file, array $metadataList, array $currentOrder): ?string
    {
        $filePos = array_search($file, $currentOrder);
        $dependencies = $metadataList[$file]->dependsOn ?? [];

        foreach ($dependencies as $dep) {
            $depPos = array_search($dep, $currentOrder);
            if ($depPos !== false && $depPos > $filePos) {
                return $dep;
            }
        }

        return null;
    }

    public static function analyzeOrder(array $orderedFiles, array $metadataList): array
    {
        $currentOrder = array_keys($metadataList);
        $analysis = [];

        foreach ($orderedFiles as $index => $file) {
            $currentPos = array_search($file, $currentOrder) + 1;
            $computedPos = $index + 1;
            $isCorrect = $currentPos === $computedPos;

            $dependencies = $metadataList[$file]->dependsOn ?? [];
            $violation = null;

            if (!$isCorrect) {
                $violation = self::findFirstViolation($file, $metadataList, $currentOrder);
            }

            $analysis[] = [
                'file' => $file,
                'current_position' => $currentPos,
                'computed_position' => $computedPos,
                'is_correct' => $isCorrect,
                'dependencies' => $dependencies,
                'violation' => $violation,
            ];
        }

        return $analysis;
    }
    
    public static function countReorderNeeded(array $analysis): int
    {
        return count(array_filter($analysis, fn($item) => !$item['is_correct']));
    }
}