<?php

use Illuminate\Support\Facades\Artisan;
use Tests\Support\Temp;
use Illuminate\Filesystem\Filesystem;

it('shows order but takes no action by default', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $fs->put($dir.'/2023_01_01_000000_create_roles_table.php', "<?php\n");
    $fs->put($dir.'/2023_01_01_000001_create_users_table.php', "<?php\n");

    $exit = Artisan::call('migrate:ordered', [
        '--path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $dir),
    ]);

    $output = Artisan::output();

    expect($exit)->toBe(0);
    expect($output)->toContain('Scanning migrations...');
    expect($output)->toContain('Computing topological order...');
    expect($output)->toContain('Safe mode: no action taken');
});
