<?php

namespace Tests\Support;

use Illuminate\Filesystem\Filesystem;

class Temp
{
    public static function makeMigrationsDir(string $name = 'tmp_migrations'): string
    {
        $fs = new Filesystem();
        $dir = base_path('tests/'.$name);
        if ($fs->isDirectory($dir)) {
            $fs->deleteDirectory($dir);
        }
        $fs->makeDirectory($dir, 0755, true);
        return $dir;
    }
}
