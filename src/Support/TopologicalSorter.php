<?php

namespace MigrationOrderer\Support;

use MigrationOrderer\Exceptions\CircularDependencyException;

class TopologicalSorter
{
    private array $graph = [];
    private array $visited = [];
    private array $visiting = [];
    private array $sorted = [];

    public function sort(array $graph): array
    {
        $this->graph = $graph;
        $this->visited = [];
        $this->visiting = [];
        $this->sorted = [];

        foreach (array_keys($graph) as $node) {
            if (!isset($this->visited[$node])) {
                $this->visit($node, []);
            }
        }

        return $this->sorted;
    }

    private function visit(string $node, array $path): void
    {
        if (isset($this->visiting[$node])) {
            $cycleStart = array_search($node, $path);
            $cycle = array_slice($path, $cycleStart);
            $cycle[] = $node;
            throw new CircularDependencyException($cycle);
        }

        if (isset($this->visited[$node])) {
            return;
        }

        $this->visiting[$node] = true;
        $path[] = $node;

        foreach ($this->graph[$node] ?? [] as $dep) {
            $this->visit($dep, $path);
        }

        unset($this->visiting[$node]);
        $this->visited[$node] = true;
        $this->sorted[] = $node;
    }
}
