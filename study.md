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
    return new $monad($v);
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
    public function __construct($v): Monad;
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

## 3. モナド則を満たす (その1)

まず `return x >>= f` == `f x` これを満たすことを確認します。


PHP的にはこうなると良いです。

```php
assert(bind(ret(1, ListMonad::class), $f) == $f(1));
```

私は勘違いしていました。`$f`の型が間違っています。

```diff
-$f = fn(int $n) => $n + 1;
+$f = fn(int $n): ListMonad => ret($n + 1, ListMonad::class);
```

よって `ListMonad::bind()` の実装はこうなります。

```diff
     /**
-     * @param Closure(T):T $f
-     * @return self<T>
+     * @param Closure(T):static<T> $f
+     * @return static<T>
      */
     public function bind(Closure $f): Monad
     {
-        return ret($f($this->v), ListMonad::class);
+        return $f($this->v);
```

なんだ、簡単じゃん……。そして、`return` って関数名の意味もわかってきたぞ。わかってきたので `ret` とか中途半端な名前はやめて `_return` にする。

```diff
modified   src/functions.php
@@ -13,7 +13,7 @@ use Closure;
  * @param class-string<M> $monad
  * @return M<T>
  */
-function ret($v, string $monad): Monad
+function _return($v, string $monad): Monad
 {
     return new $monad($v);
 }
```

するとだな…

```php
$f = fn(int $n): ListMonad =>_return($n + 1, ListMonad::class);
```

モナドに `return` とかいう変な関数名を使う気持ちがようやくわかった。C言語とかの手続き的な `return` っぽい雰囲気に似せるDSL的なやつだったんですね。
