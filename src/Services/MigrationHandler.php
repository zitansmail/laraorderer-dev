<?php

namespace MigrationOrderer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrationHandler
{
    protected string $table = 'migration_orderer';

    public function __construct()
    {
        $this->ensureTableExists();
    }

    protected function ensureTableExists(): void
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function ($table) {
                $table->id();
                $table->string('from');
                $table->string('to');
                $table->timestamp('created_at');
            });
        }
    }

    public function saveManifest(array $entries): void
    {
        $this->ensureTableExists();

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
        $this->ensureTableExists();

        return DB::table($this->table)->orderBy('id', 'asc')->get()->toArray();
    }

    public function clearManifest(): void
    {
        $this->ensureTableExists();

        DB::table($this->table)->truncate();
    }
}
