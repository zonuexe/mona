<?php

declare(strict_types=1);

use zonuexe\Mona\ListMonad;
use function zonuexe\Mona\bind;
use function zonuexe\Mona\_return;

require __DIR__ . '/vendor/autoload.php';

$list = _return(1, ListMonad::class);

var_dump($list) and \PHPStan\dumpType($list);

$f = fn(int $n): ListMonad =>_return($n + 1, ListMonad::class);
$l2 = $list->bind($f)->bind($f)->bind($f)->bind($f);
var_dump($l2) and \PHPStan\dumpType($l2);

assert(bind(_return(1, ListMonad::class), $f) == $f(1));

$m = new ListMonad(1);
$return = Closure::fromCallable('\zonuexe\Mona\_return');
assert($m->bind($return) == $m);

echo "ok", PHP_EOL;
