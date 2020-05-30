<?php

namespace RemotelyLiving\PHPCollection;

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

    public function getIterator(): \ArrayIterator;

    public function serialize(): string;

    public function unserialize($serialized, array $classnames = []);
}
