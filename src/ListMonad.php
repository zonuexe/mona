<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;
use Generator;
use IteratorAggregate;
use function array_reverse;
use function array_pop;

/**
 * @template T
 * @implements Monad<T>
 * @implements IteratorAggregate<T>
 */
class ListMonad implements Monad, IteratorAggregate
{
    /** @var T */
    private $car;
    /** @var ListMonad<T> */
    private $cdr;

    /**
     * @param T $car
     * @param ListMonad<T> $cdr
     */
    public function __construct($car, ListMonad $cdr = null)
    {
        if ($car !== null) {
            $this->car = $car;
            $this->cdr = $cdr ?? $this->nil($car);
        }
    }

    /**
     * @param T $vs
     * @return ListMonad<T>
     */
    public static function list(...$vs): ListMonad
    {
        /** @var ListMonad<T> $list */
        $list = ListMonad::nil();

        foreach (array_reverse($vs) as $v) {
            $list = ListMonad::cons($v, $list);
        }

        return $list;
    }

    /**
     * @param T $v
     * @param ListMonad<T> $list
     * @return ListMonad<T>
     */
    public static function cons($v, ListMonad $list): ListMonad
    {
        return new ListMonad($v, $list);
    }

    /**
     * @param T $v
     * @return ListMonad<T>
     */
    public static function nil($v = null): ListMonad
    {
        /** @var ListMonad<T> */
        $list = new ListMonad(null);
        return $list;
    }

    /**
     * @param Closure(T):static<T> $f
     * @return static<T>
     */
    public function bind(Closure $f): ListMonad
    {
        if ($this->car === null) {
            return $this;
        }

        return $f($this->car);
    }

    /**
     * @return Generator<T>
     */
    public function getIterator(): Generator
    {
        if ($this->car === null) {
            return;
        }

        yield $this->car;
        yield from $this->cdr;
    }

    /**
     * @param Closure(T,T):T $f
     * @param T $z
     * @return T
     */
    public function foldr(Closure $f, $z)
    {
        if ($this->car === null) {
            return $z;
        }

        return $f($this->car, $this->cdr->foldr($f, $z));
    }
}
