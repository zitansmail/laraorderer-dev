<?php 

namespace MigrationOrderer\Support;

class TopologicalSorter
{
    public function sort(array $graph): array
    {
        $visited = [];
        $sorted = [];

        $visit = function ($node) use (&$visit, &$visited, &$sorted, $graph) {
            if (isset($visited[$node])) return;
            $visited[$node] = true;
            foreach ($graph[$node] ?? [] as $dep) {
                $visit($dep);
            }
            $sorted[] = $node;
        };

        foreach (array_keys($graph) as $node) {
            $visit($node);
        }

        return $sorted;
    }
}
