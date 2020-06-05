<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection;

abstract class AbstractCollection implements CollectionInterface
{
    protected array $items;

    private \Generator $deferredValues;

    protected function __construct(array $items)
    {
        $this->items = $items;
    }

    final public static function collect(array $items = []): CollectionInterface
    {
        return new static($items);
    }

    final public static function fill(int $startIndex, int $amount, $item = null): CollectionInterface
    {
        $items = array_fill($startIndex, $amount, $item);

        return new static($items);
    }

    final public static function fromString(string $string, string $delimiter = ','): CollectionInterface
    {
        return new static(explode($delimiter, trim($string)));
    }

    final public static function later(\Generator $deferredValues): CollectionInterface
    {
        $deferred = new static([]);
        $deferred->deferredValues = $deferredValues;

        return $deferred;
    }

    final public function count(): int
    {
        return count($this->all());
    }

    /**
     * @return mixed|null
     */
    final public function first()
    {
        if ($this->empty()) {
            return null;
        }

        return $this->get(array_key_first($this->all()));
    }

    final public function last()
    {
        if ($this->empty()) {
            return null;
        }

        return $this->get(array_key_last($this->all()));
    }

    final public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->all(), $fn, $initial);
    }

    final public function union(CollectionInterface $collection): CollectionInterface
    {
        return $this->merge($collection)->unique();
    }

    final public function empty(): bool
    {
        return $this->count() === 0;
    }

    final public function all(): array
    {
        if (isset($this->deferredValues) && $this->deferredValues->valid()) {
            $this->items = Helper::unwrapDeferred($this->deferredValues);
        }

        return $this->items;
    }

    final public function deferred(): \Generator
    {
        foreach ($this->all() as $key => $value) {
            yield $key => $value;
        }
    }

    final public function values(): array
    {
        return array_values($this->all());
    }

    final public function equals(CollectionInterface $collection): bool
    {
        return $this->diff($collection)->empty();
    }

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    final public function has($offset): bool
    {
        return isset($this->all()[$offset]);
    }

    /**
     * @param mixed $item
     *
     * @return bool
     */
    final public function contains($item): bool
    {
        return in_array($item, $this->all(), true);
    }

    final public function some(callable $evaluation): bool
    {
        foreach ($this->all() as $key => $value) {
            if ($evaluation($value, $key) === true) {
                return true;
            }
        }

        return false;
    }

    final public function get($offset, $default = null)
    {
        return $this->all()[$offset] ?? $default;
    }

    final public function rand()
    {
        return $this->all()[array_rand($this->all())];
    }

    final public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    final public function serialize(): string
    {
        return \serialize(['items' => $this->all()]);
    }

    final public function unserialize($serialized, array $classnames = [])
    {
        if (isset($this->items)) {
            throw new \LogicException('Cannot unserialize instance of collection');
        }

        $this->items = \unserialize($serialized, $classnames)['items'] ?? [];
    }

    final public function jsonSerialize(): array
    {
        return $this->all();
    }
}
