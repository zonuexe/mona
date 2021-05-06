<?php

declare(strict_types=1);

use zonuexe\Mona\ListMonad;
use function zonuexe\Mona\ret;

require __DIR__ . '/vendor/autoload.php';

$list = ret(1, ListMonad::class);

var_dump($list) and \PHPStan\dumpType($list);
