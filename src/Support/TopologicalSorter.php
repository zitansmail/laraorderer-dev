<?php 

namespace MigrationOrderer\Support;

class TopologicalSorter
{
    
    private array $graph = [];
    private array $visited = [];
    private array $sorted = [];
    public function sort(array $graph): array
    {
        $this->graph = $graph;
        $this->visited = [];
        $this->sorted = [];

        foreach (array_keys($graph) as $node) {
            $this->visit($node);
        }
        return $this->sorted;
    }

    private function visit(string $node): void
    {
        if (isset($this->visited[$node])) {
            return;
        }

        $this->visited[$node] = true;

        foreach ($this->graph[$node] ?? [] as $dep) {
            $this->visit($dep);
        }

        $this->sorted[] = $node;
    }
}
