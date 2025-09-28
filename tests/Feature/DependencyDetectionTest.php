<?php

use MigrationOrderer\Services\MigrationScanner;
use Tests\Support\Temp;
use Illuminate\Filesystem\Filesystem;

it('detects foreign key dependencies correctly', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $fs->put($dir.'/2023_01_01_000000_create_users_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("users", function (Blueprint $table) {
                    $table->id();
                    $table->string("name");
                    $table->timestamps();
                });
            }
        };
');

    $fs->put($dir.'/2023_01_01_000001_create_posts_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("posts", function (Blueprint $table) {
                    $table->id();
                    $table->string("title");
                    $table->foreignId("user_id")->constrained();
                    $table->timestamps();
                });
            }
        };
    ');

    $scanner = new MigrationScanner();
    $metadata = $scanner->scan($dir);

    $usersFile = $dir.'/2023_01_01_000000_create_users_table.php';
    $postsFile = $dir.'/2023_01_01_000001_create_posts_table.php';

    expect($metadata)->toHaveCount(2);
    expect($metadata[$usersFile]->dependsOn)->toBeEmpty();
    expect($metadata[$postsFile]->dependsOn)->toContain($usersFile);
});

it('handles missing dependencies', function () {
    $fs = new Filesystem();
    $dir = Temp::makeMigrationsDir();

    $fs->put($dir.'/2023_01_01_000000_create_orders_table.php', '<?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up() {
                Schema::create("orders", function (Blueprint $table) {
                    $table->id();
                    $table->foreignId("customer_id")->constrained();
                    $table->timestamps();
                });
            }
        };
    ');

    $scanner = new MigrationScanner();
    $metadata = $scanner->scan($dir);

    $ordersFile = $dir.'/2023_01_01_000000_create_orders_table.php';

    expect($metadata[$ordersFile]->missing)->toContain('customers');
});