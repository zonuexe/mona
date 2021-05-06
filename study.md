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
