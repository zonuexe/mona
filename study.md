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

## 4. モナド則を満たす (その2)

次です。 `m >>= return` == `m` らしいです。よっしゃ。

```php
$m = new ListMonad(1);
$return Closure::fromCallable('\zonuexe\Mona\_return');
assert($m->bind($return) == $m);
```

だめです。

```
PHP Fatal error:  Uncaught ArgumentCountError: Too few arguments to function zonuexe\Mona\_return(), 1 passed in /Users/megurine/repo/php/mona/src/ListMonad.php on line 33 and exactly 2 expected in /Users/megurine/repo/php/mona/src/functions.php:16
```

そうです。オレオレ `return` は型付けの要請を満たすために2引数関数になっていたのです。しょうがないにゃあ。

```diff
@@ -13,8 +18,17 @@ use Closure;
  * @param class-string<M> $monad
  * @return M<T>
  */
-function _return($v, string $monad): Monad
+function _return($v, string $monad = null): Monad
 {
+    if ($monad === null) {
+        $ref_type = get_caller_type(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]);
+        if ($ref_type === null) {
+            throw new BadFunctionCallException('Please call me from statically typed method');
+        }
+
+        $monad = $ref_type->getName();
+    }
+
+    if (!is_a($monad, Monad::class, true)) {
+        throw new BadFunctionCallException('Class name $monad must be implements Monad');
+    }
+
     return new $monad($v);
 }
```

PHPならではの邪道感がありますね… `get_caller_type()` はリフレクションを使って、呼び出し元の関数またはメソッドの型を取得します。

再び検証コードを再掲します。

```php
$m = new ListMonad(1);
$return Closure::fromCallable('\zonuexe\Mona\_return');
assert($m->bind($return) == $m);
```

`_return`は誰が呼ぶのでしょうか？ そうです。 `ListMonad::bind()` です。つまり定義はこのように修正しなければいけません。

```diff
modified   src/ListMonad.php
@@ -28,7 +28,7 @@ class ListMonad implements Monad
      * @param Closure(T):static<T> $f
      * @return static<T>
      */
-    public function bind(Closure $f): Monad
+    public function bind(Closure $f): ListMonad
     {
         return $f($this->v);
     }
```

ばっちりですね…！

## 5. ListMonadをリストにする

さて、何も考えなくListMonadを定義しはじめたのですが、まだリストっぽい「物を並べる」という能力はありません。物、並べたくないですか……？

物を並べるとき、PHPではarrayを使います。ここでは「リスト」と呼ばれるデータ構造を考えます。「リスト」は配列と同じものを指して呼ぶこともありますが、ここでは単方向リストというデータ構造を作ります。

今回作りたいリストは、以下のように物を列挙できる構造です。

```php
(1, 2, 3, 4, 5)
```

リストの作りかたは二要素を保持できるデータ構造の入れ子構造で表現できます。たとえばPHPの配列で表現するとこうです。

```php
// (1, 2, 3, 4, 5)
$lst1 = [1, [2, [3, [4, [5, null]]]]];
```

長さ1以上のリストは玉ねぎのように後続する要素を持っているので、リストの先頭に要素を追加するのはめっちゃ簡単です。

```php
// (0, 1, 2, 3, 4, 5)
$lst2 = [0, $lst1];
```

もちろんPHPの配列はいくつでもデータを格納できますが、あえて2要素しか使わない縛りをするとこうなる、ということです。

では今回の `ListMonad` ではどうすればいいでしょうか。保持するデータを以下のように変えます。

```diff
 class ListMonad implements Monad
 {
     /** @var T */
-    private $v;
+    private $car;
+    /** @var ListMonad<T> */
+    private $cdr;
+
+    /**
+     * @param T $car
+     * @param ListMonad<T> $cdr
+     */
+    public function __construct($car, ListMonad $cdr = null)
+    {
+        if ($car !== null) {
+            $this->car = $car;
+            $this->cdr = $cdr ?? $this->nil($car);
+        }
```

いままで `$v` というプロパティにデータを保持していましたが、今回は `$car` と `$cdr` という2要素にデータを持ちます。このCARとCDRという用語にはそんなに意味はないです。Lispではそう呼ばれる、くらいの感じ。

`$this->nil($car)` はリスト終端です。さきほどの配列は `null` を使っていました。`ListMonad::nil()` は定義はこうです。

```php
    /**
     * @param T $v
     * @return ListMonad<T>
     */
    public static function nil($v): ListMonad
    {
        /** @var ListMonad<T> */
        $list = new ListMonad(null);
        return $list;
    }
```

`$v` は使ってないのですが、 `ListMonad<int>` のような型を付けるためにダミーで渡しています。

リストは一気に作りたいので、こういうヘルパー的な静的メソッドを作ります。

```php
    /**
     * @param T $vs
     * @return ListMonad<T>
     */
    public static function list(...$vs): ListMonad
    {
        $v = array_pop($vs);
        assert($v !== null);
        $list = new ListMonad($v);
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
```

つまり、こう使えるようにします。

```php
$lst1 = ListMonad::list(1, 2, 3, 4, 5);
$lst2 = ListMonad::cons(0, $lst1);

var_dump(iterator_to_array($lst1, false));
var_dump(iterator_to_array($lst2, false));
```

PHPの `iterator_to_array()` が使えるようにこうします。

```diff
 namespace zonuexe\Mona;
 
 use Closure;
+use Generator;
+use IteratorAggregate;
+use function array_reverse;
+use function array_pop;
 
 /**
  * @template T
  * @implements Monad<T>
+ * @implements IteratorAggregate<T>
  */
 class ListMonad implements Monad
 {
@@ -78,4 +83,17 @@ class ListMonad implements Monad
 
         return $f($this->car);
     }
+
+    /**
+     * @return Generator<T>
+     */
+    public function getIterator(): Generator
+    {
+        if ($this->car === null) {
+            return;
+        }
+
+        yield $this->car;
+        yield from $this->cdr;
+    }
 }
```

やったあ、これでちゃんとPHPでも使えるリストになったぜ。

……さて、なんで `$this->cdr = null` ではなく `$this->car = $this->nil($v)` にするのでしょうか？

空リストに対する操作もできると便利だからですね。

```diff
@@ -77,6 +77,10 @@ class ListMonad implements Monad, IteratorAggregate
      */
     public function bind(Closure $f): ListMonad
     {
+        if ($this->car === null) {
+            return $this;
+        }
+
         return $f($this->car);
     }
```

やりましたよ。

## 6. リストっぽい手続きを用意する


こうしたい。

```php
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
```

`lst\foldl()` はリストの左側から、 `lst\foldr()` はリストの右側から処理する。われわれの知ってる足し算は順序に依存しないので、どちらも `55` が返れば成功。どっちがいいの？ってやつはぐぐればいろいろ出てくる。PHPだと`array_reduce()`を使うやつ。

`foldl`は脳死で実装。

```php
/**
 * @template T
 * @param Closure(T,T):T $f
 * @param T $z
 * @param ListMonad<T> $xs
 * @return T
 */
function foldl(Closure $f, $z, ListMonad $xs)
{
    $succ = $z;
    foreach ($xs as $x) {
        $succ = $f($succ, $x);
    }

    return $succ;
}
```

`foldr`はメソッドに任せる。

```php
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
```

`$this->car === null` はいままでも定義に何回か出てるけど、空リストかどうか、つまりリストの終端かどうかの判定です。空リストに辿り着くまでは再帰呼び出ししつづけ、底までたどりつくとようやく値を返します。

`foldr`ができれば`join`、つまりPHPの`array_merge()`みたいなやつが実装できそうですが、眠いので後回しにします。
