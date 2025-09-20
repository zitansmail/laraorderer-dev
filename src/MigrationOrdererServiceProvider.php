<?php  

namespace MigrationOrderer;

use Illuminate\Support\ServiceProvider;
use MigrationOrderer\Commands\OrderedMigrateCommand;

class MigrationOrdererServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/migration_orderer.php', 'migration_orderer');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                OrderedMigrateCommand::class,
            ]);
            $this->publishesMigrations([
                __DIR__.'../database/migrations' => database_path('migrations')
            ]);
            $this->publishes([
                __DIR__.'/../config/migration_orderer.php' => config_path('migration_orderer.php'),
            ], 'config');
        }
    }
}