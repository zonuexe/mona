<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use BadFunctionCallException;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

use function debug_backtrace;
use function is_a;

/**
 * @template T
 * @template M of Monad
 * @param T $v
 * @param class-string<M> $monad
 * @return M<T>
 */
function _return($v, string $monad = null): Monad
{
    if ($monad === null) {
        $ref_type = get_caller_type(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]);
        if ($ref_type === null) {
            throw new BadFunctionCallException('Please call me from statically typed method');
        }

        $monad = $ref_type->getName();
    }

    if (!is_a($monad, Monad::class, true)) {
        throw new BadFunctionCallException('Class name $monad must be implements Monad');
    }

    return new $monad($v);
}

/**
 * @template T
 * @template M of Monad<T>
 * @param M $m
 * @param Closure(T):T $f
 * @return M<T>
 */
function bind(Monad $m, Closure $f): Monad
{
    return $m->bind($f);
}

/**
 * @param array{class?:class-string,function?:string} $backtrace
 */
function get_caller_type(array $backtrace): ?ReflectionNamedType
{
    $ref_type = null;
    if (isset($backtrace['class'], $backtrace['function'])) {
        $ref_class = new ReflectionClass($backtrace['class']);
        $ref_method = $ref_class->getMethod($backtrace['function']);
        $ref_type = $ref_method->getReturnType();
    } elseif ($backtrace['function']) {
        $ref_function = new ReflectionFunction($backtrace['function']);
        $ref_type = $ref_function->getReturnType();
    }

    if ($ref_type === null) {
        return null;
    }

    assert($ref_type instanceof ReflectionNamedType);

    return $ref_type;
}
