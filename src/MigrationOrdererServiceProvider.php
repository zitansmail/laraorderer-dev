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
        if ($this->app->runningInConsole()) {
            $this->commands([
                OrderedMigrateCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/migration_orderer.php' => config_path('migration_orderer.php'),
            ], 'config');
        }
    }
}