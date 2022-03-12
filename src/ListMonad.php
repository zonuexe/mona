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
    final public function __construct($car, ListMonad $cdr = null)
    {
        if ($car !== null) {
            $this->car = $car;
            $this->cdr = $cdr ?? $this->nil($car);
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function __debugInfo(): array
    {
        return $this->null()
            ? ['NIL' => null]
            : ['car' => $this->car, 'cdr' => $this->cdr];
    }

    /**
     * @template TValue
     * @param TValue $vs
     * @return ListMonad<TValue>
     */
    public static function list(...$vs): ListMonad
    {
        /** @var ListMonad<TValue> $list */
        $list = ListMonad::nil();

        foreach (array_reverse($vs) as $v) {
            $list = ListMonad::cons($v, $list);
        }

        return $list;
    }

    /**
     * @template TValue
     * @param TValue $v
     * @param ListMonad<TValue> $list
     * @return ListMonad<TValue>
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
     * Return true if $this is NIL object (empty list)
     */
    public function null(): bool
    {
        return $this->car === null;
    }

    /**
     * @template T2
     * @param Closure(T):static<T2> $f
     * @return static<T2>
     */
    public function bind(Closure $f): ListMonad
    {
        if ($this->null()) {
            /** @var static<T2> */
            $nil = self::nil();

            return $nil;
        }

        if ($this->cdr->null()) {
            return $f($this->car);
        }

        return $f($this->car)->concat($this->cdr->bind($f));
    }

    /**
     * @param static<T> $list
     * @return static<T>
     */
    public function concat(ListMonad $list): ListMonad
    {
        return $this->null() || $this->cdr->null()
            ? new static($this->car, $list)
            : new static($this->car, $this->cdr->concat($list));
    }

    /**
     * @template TValue
     * @param TValue $value
     * @return static<TValue>
     */
    public static function unit($value): ListMonad
    {
        return new ListMonad($value);
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
