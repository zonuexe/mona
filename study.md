# モナドをわかる

## 1. モナドが何かわからないけど入れ物を作る

よくわからんので、まずは7shiさんの[モナド則がちょっと分かった？ - Qiita](https://qiita.com/7shi/items/547b6137d7a3c482fe68)をもとに真似する。

```haskell
Prelude> return 1 :: [] Int
[1]
```

Haskellの `return` に値を渡すとモナドになるらしい。PHPで `return` なんて予約語と同じ関数は用意できないので、 `ret` と呼ぶことにする。いいね？

```php
namespace zonuexe\Monad;

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
```

いま作りたいのはモナドの入れ物だけなので、定義はこう。

```php
/**
 * @template T
 */
interface Monad
{
    /**
     * @param T $v
     * @return Monad<T>
     */
    public static function new($v): Monad;
}
```

実装はこう。

```php
/**
 * @template T
 * @implements Monad<T>
 */
class ListMonad implements Monad
{
    /** @var T */
    private $v;

    /**
     * @param T $v
     */
    private function __construct($v)
    {
        $this->v = $v;
    }

    /**
     * @param T $v
     * @return self<T>
     */
    public static function new($v): self
    {
        return new self($v);
    }
}
```

使い方は、まずはこれだけ。

```php
$list = ret(1, ListMonad::class);
```

## 2. bindを実装してみる

```haskell
Prelude> let f x = return $ x + 1
Prelude> [1] >>= f
[2]
Prelude> [1] >>= f >>= f >>= f >>= f
[5]
```

PHPで書くとこう。

```php
$f = fn(int $n) => $n + 1;
$l2 = $list->bind($f)->bind($f)->bind($f)->bind($f);
```

`ListMonad::bind()` を実装してみる。

```php
<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;
use function zonuexe\Mona\ret;

/**
 * @template T
 * @implements Monad<T>
 */
class ListMonad implements Monad
{
    /** @var T */
    private $v;

    /**
     * @param T $v
     */
    private function __construct($v)
    {
        $this->v = $v;
    }

    /**
     * @param T $v
     * @return self<T>
     */
    public static function new($v): self
    {
        return new self($v);
    }

    /**
     * @param Closure(T):T $f
     * @return self<T>
     */
    public function bind(Closure $f): Monad
    {
        return ret($f($this->v), ListMonad::class);
    }
}
```

動くじゃん。


