<?php

use MigrationOrderer\Support\TopologicalSorter;
use MigrationOrderer\Exceptions\CircularDependencyException;

it('detects circular dependencies', function () {
    $graph = [
        'file_a.php' => ['file_b.php'],
        'file_b.php' => ['file_c.php'],
        'file_c.php' => ['file_a.php'],
    ];

    $sorter = new TopologicalSorter();

    expect(fn() => $sorter->sort($graph))
        ->toThrow(CircularDependencyException::class);
});

it('sorts complex dependency graph correctly', function () {
    $graph = [
        'file_a.php' => [],
        'file_b.php' => ['file_a.php'],
        'file_c.php' => ['file_a.php'],
        'file_d.php' => ['file_b.php', 'file_c.php'],
    ];

    $sorter = new TopologicalSorter();
    $result = $sorter->sort($graph);

    $posA = array_search('file_a.php', $result);
    $posB = array_search('file_b.php', $result);
    $posC = array_search('file_c.php', $result);
    $posD = array_search('file_d.php', $result);

    expect($posA)->toBeLessThan($posB);
    expect($posA)->toBeLessThan($posC);
    expect($posB)->toBeLessThan($posD);
    expect($posC)->toBeLessThan($posD);
});