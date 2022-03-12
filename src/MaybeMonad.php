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
     * @template TValue
     * @param TValue $value
     * @return MaybeMonad<TValue>
     */
    public static function unit(mixed $value): self
    {
        return new Just($value);
    }
}
