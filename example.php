<?php

declare(strict_types=1);

use zonuexe\Mona\ListMonad;
use function zonuexe\Mona\ret;

require __DIR__ . '/vendor/autoload.php';

$list = ret(1, ListMonad::class);

var_dump($list) and \PHPStan\dumpType($list);

$f = fn(int $n) => $n + 1;
$l2 = $list->bind($f)->bind($f)->bind($f)->bind($f);
var_dump($l2) and \PHPStan\dumpType($l2);
