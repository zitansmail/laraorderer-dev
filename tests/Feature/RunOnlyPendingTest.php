<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use MigrationOrderer\Tests\Support\Temp;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

it('runs only pending migrations when --run is provided', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $m1 = '2023_01_01_000000_create_roles_table.php';
    $m2 = '2023_01_01_000001_create_users_table.php';

    $fs->put($dir.'/'.$m1, "<?php\n");
    $fs->put($dir.'/'.$m2, "<?php\n");

    // Simulate "roles" already migrated
    DB::table('migrations')->insert([
        'migration' => Str::before($m1, '.php'),
        'batch' => 1,
    ]);

    // We don't want to actually execute Laravel migrator in tests here.
    // Instead, assert that the command invokes the runner's logic which would skip m1 and try m2.
    // Quick check: just ensure it doesn't crash and prints "Skipping" for m1.
    $exit = Artisan::call('migrate:ordered', [
        '--path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $dir),
        '--run'  => true,
        '--force'=> true,
    ]);

    $output = Artisan::output();
    expect($exit)->toBe(0);
    expect($output)->toContain('Skipping (already migrated): '.$m1);
    // It should attempt to run m2 (your runner prints "Running:")
    expect($output)->toContain('Running: '.$m2);
});
