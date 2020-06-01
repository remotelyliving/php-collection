<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection;

final class Collection implements CollectionInterface
{
    private array $items;

    private \Generator $deferredValues;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function collect(array $items = []): self
    {
        self::validateItems($items);

        return new static($items);
    }

    public static function fill(int $startIndex, int $amount, $item = null): self
    {
        self::validateItem($item);

        $items = array_fill($startIndex, $amount, $item);

        return new self($items);
    }

    public static function fromString(string $string, string $delimiter = ','): self
    {
        return new self(explode($delimiter, trim($string)));
    }

    public static function later(\Generator $deferredValues): self
    {
        $deferred = new static([]);
        $deferred->deferredValues = $deferredValues;

        return $deferred;
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function map(callable $fn): self
    {
        return new static(array_map($fn, $this->all()));
    }

    public function filter(callable $fn): self
    {
        return new static(array_filter($this->all(), $fn, ARRAY_FILTER_USE_BOTH));
    }

    public function each(callable $fn): self
    {
        $deferredValues = function () use ($fn) {
            foreach ($this->all() as $key => $item) {
                yield $key => $fn($item, $key);
            }
        };

        return self::later($deferredValues());
    }

    public function reverse(): self
    {
        return new static(array_reverse($this->all()));
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        if ($this->empty()) {
            return null;
        }

        return $this->get(array_key_first($this->all()));
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        if ($this->empty()) {
            return null;
        }

        return $this->get(array_key_last($this->all()));
    }

    /**
     * @param callable $fn
     * @param null     $initial
     *
     * @return mixed
     */
    public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->all(), $fn, $initial);
    }

    public function unique(): self
    {
        return new self(array_unique($this->all()));
    }

    public function diff(Collection $collection, callable $comparator = null): self
    {
        return new self(array_udiff($this->all(), $collection->all(), $comparator ?? self::getObjectSafeComparator()));
    }

    public function merge(Collection $collection): self
    {
        return new self(array_merge($this->all(), $collection->all()));
    }

    public function union(Collection $collection): self
    {
        return $this->merge($collection)->unique();
    }

    public function intersect(Collection $collection): self
    {
        return new self(array_intersect($this->all(), $collection->all()));
    }

    public function sort(callable $comparator = null): self
    {
        $items = $this->all();
        uasort($items, $comparator ?? self::getObjectSafeComparator());

        return new self($items);
    }

    public function kSort(callable $comparator = null): self
    {
        $items = $this->all();
        uksort($items, $comparator ?? self::getStringComparator());

        return new self($items);
    }

    public function empty(): bool
    {
        return $this->count() === 0;
    }

    public function all(): array
    {
        if (isset($this->deferredValues) && $this->deferredValues->valid()) {
            $this->items = $this->unwrapDeferred();
        }

        return $this->items;
    }

    public function reIndex(): self
    {
        return new self($this->values());
    }

    public function chunk(int $size, callable $fn): self
    {
        $deferred = function () use ($size, $fn) {
            foreach (array_chunk($this->all(), $size, true) as $chunk) {
                foreach ($chunk as $index => $value) {
                    yield $index => $fn($value, $index);
                }
            }
        };

        // do the work later and revalidate processed values
        return self::later($deferred());
    }

    public function deferred(): \Generator
    {
        foreach ($this->all() as $key => $value) {
            yield $key => $value;
        }
    }

    public function values(): array
    {
        return array_values($this->all());
    }

    public function equals(Collection $collection): bool
    {
        return $this->diff($collection)->empty();
    }

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function has($offset): bool
    {
        return isset($this->all()[$offset]);
    }

    /**
     * @param mixed $item
     *
     * @return bool
     */
    public function contains($item): bool
    {
        return in_array($item, $this->all(), true);
    }

    public function some(callable $evaluation): bool
    {
        foreach ($this->all() as $key => $value) {
            if ($evaluation($value, $key) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|int $offset
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function get($offset, $default = null)
    {
        return $this->all()[$offset] ?? $default;
    }

    /**
     * @return mixed
     */
    public function rand()
    {
        return $this->all()[array_rand($this->all())];
    }

    /**
     * @param string|int $offset
     */
    public function remove(...$offsets): self
    {
        $withRemoved = new static($this->all());
        foreach ($offsets as $offset) {
            unset($withRemoved->items[$offset]);
        }

        return $withRemoved;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->all());
    }

    public function serialize(): string
    {
        return \serialize(['items' => $this->all()]);
    }

    public function unserialize($serialized, array $classnames = [])
    {
        if (isset($this->items)) {
            throw new \LogicException('Cannot unserialize instance of collection');
        }

        $this->items = \unserialize($serialized, $classnames)['items'] ?? [];
    }

    public function jsonSerialize(): array
    {
        return $this->all();
    }

    private function unwrapDeferred(): array
    {
        $items = [];
        foreach ($this->deferredValues as $key => $item) {
            self::validateItem($item);
            $items[$key] = $item;
        }

        return $items;
    }

    /**
     * @throw \InvalidArgumentException
     */
    private static function validateItems(array $items): void
    {
        foreach ($items as $item) {
            self::validateItem($item);
        }
    }

    private static function validateItem($item): void
    {
        if (is_iterable($item)) {
            throw new \InvalidArgumentException('A collection may only contain numbers, strings, or objects');
        }
    }

    private static function getStringComparator(): callable
    {
        return function ($a, $b): int {
            return strcmp((string) $a, (string) $b);
        };
    }

    private static function getObjectSafeComparator(): callable
    {
        return function ($a, $b): int {
            return (\gettype($a) === \gettype($b)) ? $a <=> $b : -1;
        };
    }
}
