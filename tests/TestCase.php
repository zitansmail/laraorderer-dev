<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use MigrationOrderer\MigrationOrdererServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

abstract class TestCase extends BaseTestCase
{
        protected function getPackageProviders($app)
    {
        return [MigrationOrdererServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel "migrations" table used by MigrationRunner checks
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
        }

        // Our DB-backed manifest table
        if (!Schema::hasTable('migration_orderer')) {
            Schema::create('migration_orderer', function (Blueprint $table) {
                $table->id();
                $table->string('from');
                $table->string('to');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }
}
