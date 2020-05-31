[![Build Status](https://travis-ci.com/remotelyliving/php-collection.svg?branch=master)](https://travis-ci.org/remotelyliving/php-collection)
[![Total Downloads](https://poser.pugx.org/remotelyliving/php-collection/downloads)](https://packagist.org/packages/remotelyliving/php-collection)
[![Coverage Status](https://coveralls.io/repos/github/remotelyliving/php-collection/badge.svg?branch=master)](https://coveralls.io/github/remotelyliving/php-collection?branch=master) 
[![License](https://poser.pugx.org/remotelyliving/php-collection/license)](https://packagist.org/packages/remotelyliving/php-collection)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/remotelyliving/php-collection/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/remotelyliving/php-collection/?branch=master)

# php-collection
### A lightweight, immutable collection object for set operations

### Use Cases

So there is already a really nice collection library out there. I wanted to write my own for two reasons.

1. I needed less than half of what it offered.
2. It's fun!
2.1 Libraries that load themselves globally as level functions are evil

But also,

If you're tired of using arrays everywhere, this might be for you.
If you're jealous of JavaScript's really gorgeous array and object API, this might be for you
If the mutability and utter chaos of passing an array around scares you, this might be for you

### Installation

```sh
composer require remotelyliving/php-collection
```

### Usage

#### Collect::collect

Collections can be sets of numbers, strings, or objects. No arrays allowed.
We don't want to get into multi dimensional madness.

```php
use RemotelyLiving\PHPCollection\Collection;

$collectionOfStrings = Collection::collect(['foo', 'bar', 'baz']);
$collectionOfNumbers = Collection::collect([1, 2, 3]);
$collectionOfUsers = Collection::collect([$id1 => $user1, $id2 => $user2, $id4 => $user3]);

// we can iterate over collections
foreach ($collectionOfNumbers as $number) {
    echo (string) $number;
}
// outputs 1, 2, 3

// we can also perform set operations
$collectionOfUsers->contains($user1); // true
$collectionOfUsers->has($id1); // true
$collectionOfUsers->get($id1, new User()); // defaults to a new User if not found
$collectionOfUsers->some(fn(User $user) => $user->isVerified()); // true if some users are verified

$collectionOfUsers->filter(fn(User $user, $index) => $index > 1)
    ->map(fn(User $user) => $user->getName())
    ->first();

$collectionOfNumbers->reverse()
    ->all();

$collectionOfNumbers->each(fn(int $number, int $index) => $number * $index)
    ->unique()
    ->all();
```

#### Collection::later

We can create collections that don't don't fill up until we want something from them.

```php
$generator = fn() => yield 1;
$deferredCollect = Collection::later($generator());
```

#### Collection::fill

We can create a prefilled collection

```php
$filled = Collection::fill(0, 100, 'hi!');
```

#### Collection::fromString

We can create a collection from a delineated string

```php
$fromString = Collection::fromString('1|2|3|4|5', '|');
```

#### Collection Set Methods

Now to the good stuff!
All evaluation or operating methods that take a callable always pass in the value first, then the key.
Below is the whole object API. It provides a lot hopefully without overstepping.

You can loop over it just like an array safely. It's `\Traversable` and `iterable`
You can also safely `serialize()` and `json_encode()` it.

```php

interface CollectionInterface extends \Traversable, \Countable, \IteratorAggregate, \Serializable, \JsonSerializable
{
    public function count(): int;

    public function map(callable $fn): self;

    public function filter(callable $fn): self;

    public function each(callable $fn): self;

    public function reverse(): self;

    /**
     * @return mixed|null
     */
    public function first();

    /**
     * @return mixed|null
     */
    public function last();

    /**
     * @param callable $fn
     * @param null     $initial
     *
     * @return mixed
     */
    public function reduce(callable $fn, $initial = null);

    public function unique(): self;

    public function diff(Collection $collection): self;

    public function merge(Collection $collection): self;

    public function union(Collection $collection): self;

    public function intersect(Collection $collection): self;

    public function sort(callable $comparator = null): self;

    public function kSort(callable $comparator = null): self;

    public function empty(): bool;

    public function all(): array;

    public function deferred(): \Generator;

    public function values(): array;

    public function equals(Collection $collection): bool;

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function has($offset): bool;

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($value): bool;

    public function some(callable $evaluation): bool;

    /**
     * @param string|int $offset
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($offset, $default = null);

    /**
     * @return mixed
     */
    public function rand();

    /**
     * @param string|int ...$offset
     */
    public function remove(...$offset): self;
}
```