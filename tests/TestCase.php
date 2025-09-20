<?php

namespace MigrationOrderer\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use MigrationOrderer\MigrationOrdererServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [MigrationOrdererServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // sqlite in-memory
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp(); // <<< IMPORTANT: boot the app first

        // Create Laravel "migrations" table used by migrator
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
        }

        // Your package’s DB-backed manifest table
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
