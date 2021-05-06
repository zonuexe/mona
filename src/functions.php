<?php

declare(strict_types=1);

namespace zonuexe\Mona;

/**
 * @template T
 * @template M of Monad
 * @param T $v
 * @param class-string<M> $monad
 * @return M<T>
 */
function ret($v, string $monad): Monad
{
    return $monad::new($v);
}
