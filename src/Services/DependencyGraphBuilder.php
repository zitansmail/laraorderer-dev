<?php 
namespace MigrationOrderer\Services;

use MigrationOrderer\Data\MigrationMetadata;

class DependencyGraphBuilder
{
    public function build(array $metadataList): array
    {
        $graph = [];
        foreach ($metadataList as $path => $meta) {
            $graph[$path] = $meta->dependsOn;
        }
        return $graph;
    }
}
