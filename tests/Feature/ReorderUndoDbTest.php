<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Support\Temp;
use Illuminate\Filesystem\Filesystem;

it('stores manifest rows on reorder and can undo them', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $a = $dir.'/2023_01_01_000000_create_roles_table.php';
    $b = $dir.'/2023_01_01_000001_create_users_table.php';
    $fs->put($a, "<?php\nSchema::create('roles', function(){});");
    $fs->put($b, "<?php\nSchema::create('users', function(){});");

    $exit1 = Artisan::call('migrate:ordered', [
        '--path'    => str_replace(base_path().DIRECTORY_SEPARATOR, '', $dir),
        '--reorder' => true,
        '--force'   => true,
    ]);
    expect($exit1)->toBe(0);

    $rows = DB::table('migration_orderer')->get();
    expect($rows->count())->toBeGreaterThan(0);

    $exit2 = Artisan::call('migrate:ordered', [
        '--path'      => str_replace(base_path().DIRECTORY_SEPARATOR, '', $dir),
        '--undo-last' => true,
        '--force'     => true,
    ]);
    expect($exit2)->toBe(0);

    $rowsAfter = DB::table('migration_orderer')->get();
    expect($rowsAfter->count())->toBe(0);

    expect($fs->exists($a))->toBeTrue();
    expect($fs->exists($b))->toBeTrue();
});