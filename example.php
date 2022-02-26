<?php

declare(strict_types=1);

use zonuexe\Mona\ListMonad;
use zonuexe\Mona\lst;
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
assert($m->bind($return) === $m);

var_dump(lst\foldl(
    fn(int $n, int $m): int => $n + $m,
    0,
    ListMonad::list(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
));

var_dump(lst\foldr(
    fn(int $n, int $m): int => $n + $m,
    0,
    ListMonad::list(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
));

// var_dump(iterator_to_array(ListMonad::list(1, 2, 3, 4, 5, 6, 7, 8, 9, 10), false));

echo "ok", PHP_EOL;
