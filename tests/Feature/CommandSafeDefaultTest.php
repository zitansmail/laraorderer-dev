<?php

use Illuminate\Support\Facades\Artisan;
use Tests\Support\Temp;
use Illuminate\Filesystem\Filesystem;

it('shows order but takes no action by default', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $fs->put($dir . '/2023_01_01_000000_create_roles_table.php', "<?php\n");
    $fs->put($dir . '/2023_01_01_000001_create_users_table.php', "<?php\n");

    $exit = Artisan::call('migrate:ordered', [
        '--path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $dir),
    ]);

    $output = Artisan::output();

    expect($exit)->toBe(0);
    expect($output)->toContain('Scanning migrations...');
    expect($output)->toContain('Computing topological order...');
    expect($output)->toContain('Safe mode: no action taken');
});

it('displays enhanced preview with clean migration names', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    // Create migrations in wrong order to test preview
    $fs->put($dir . '/2023_01_01_000000_create_posts_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("posts", function (Blueprint $table) {
                    $table->id();
                    $table->foreignId("user_id")->constrained();
                });
            }
        };
    ');

    $fs->put($dir . '/2023_01_01_000001_create_users_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("users", function (Blueprint $table) {
                    $table->id();
                    $table->string("name");
                });
            }
        };
    ');

    $exit = Artisan::call('migrate:ordered', [
        '--path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $dir),
        '--preview' => true
    ]);

    $output = Artisan::output();

    expect($exit)->toBe(0);

    expect($output)->toContain('Migration');
    expect($output)->toContain('Current Pos');
    expect($output)->toContain('Status');

    expect($output)->toContain('create_users_table');
    expect($output)->toContain('create_posts_table');
    expect($output)->not->toContain('2023_01_01_000000');

    expect($output)->toContain('NEEDS REORDER');
    expect($output)->toContain('migration(s) need reordering');
});

it('shows OK status when migrations are in correct order', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $fs->put($dir . '/2023_01_01_000000_create_users_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("users", function (Blueprint $table) {
                    $table->id();
                    $table->string("name");
                });
            }
        };
    ');

    $fs->put($dir . '/2023_01_01_000001_create_posts_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("posts", function (Blueprint $table) {
                    $table->id();
                    $table->foreignId("user_id")->constrained();
                });
            }
        };
    ');

    $exit = Artisan::call('migrate:ordered', [
        '--path' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $dir),
        '--preview' => true
    ]);

    $output = Artisan::output();

    expect($exit)->toBe(0);
    expect($output)->toContain('All migrations are in dependency-safe order');
});
