<?php 

namespace MigrationOrderer\Data;

class MigrationMetadata {
    public string $filename;
    public array $dependsOn;
    public array $missing;
    public array $tablesCreated;  // NEW

    public function __construct(string $filename, array $dependsOn = [], array $missing = [], array $tablesCreated = [])
    {
        $this->filename = $filename;
        $this->dependsOn = $dependsOn;
        $this->missing = $missing;
        $this->tablesCreated = $tablesCreated;
    }
}
