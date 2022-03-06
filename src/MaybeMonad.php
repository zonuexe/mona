<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use zonuexe\Mona\Monad;
use zonuexe\Mona\MaybeMonad\Just;

/**
 * @template T
 * @implements Monad<T>
 */
abstract class MaybeMonad implements Monad
{
    /**
     * @param T $value
     * @return MaybeMonad<T>
     */
    public static function unit(mixed $value): self
    {
        return new Just($value);
    }
}
