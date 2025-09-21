<?php

namespace MigrationOrderer\Services;

use Illuminate\Support\Facades\DB;

class MigrationHandler
{
    protected string $table = 'migration_orderer';

    public function saveManifest(array $entries): void
    {
        foreach ($entries as $entry) {
            DB::table($this->table)->insert([
                'from' => $entry['from'],
                'to'   => $entry['to'],
                'created_at' => now(),
            ]);
        }
    }

    public function readManifest(): array
    {
        return DB::table($this->table)->orderBy('id', 'asc')->get()->toArray();
    }

    public function clearManifest(): void
    {
        DB::table($this->table)->truncate();
    }
}
