<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection;

final class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    public function map(callable $fn): CollectionInterface
    {
        return new self(array_map($fn, $this->all()));
    }

    /**
     * @inheritDoc
     */
    public function filter(callable $fn): CollectionInterface
    {
        return new self(array_filter($this->all(), $fn, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @inheritDoc
     */
    public function each(callable $fn): CollectionInterface
    {
        $deferredValues = function () use ($fn) {
            foreach ($this->all() as $key => $item) {
                yield $key => $fn($item, $key);
            }
        };

        return self::later($deferredValues());
    }

    /**
     * @inheritDoc
     */
    public function reverse(): CollectionInterface
    {
        return new self(array_reverse($this->all()));
    }

    /**
     * @inheritDoc
     */
    public function unique(): CollectionInterface
    {
        return new self(array_unique($this->all()));
    }

    /**
     * @inheritDoc
     */
    public function diff(CollectionInterface $collection, callable $comparator = null): CollectionInterface
    {
        return new self(
            array_udiff($this->all(), $collection->all(), $comparator ?? Helper::getObjectSafeComparator())
        );
    }

    /**
     * @inheritDoc
     */
    public function merge(CollectionInterface $collection): CollectionInterface
    {
        return new self(array_merge($this->all(), $collection->all()));
    }

    /**
     * @inheritDoc
     */
    public function intersect(CollectionInterface $collection): CollectionInterface
    {
        return new self(array_intersect($this->all(), $collection->all()));
    }

    /**
     * @inheritDoc
     */
    public function sort(callable $comparator = null): CollectionInterface
    {
        $items = $this->all();
        uasort($items, $comparator ?? Helper::getObjectSafeComparator());

        return new self($items);
    }

    /**
     * @inheritDoc
     */
    public function kSort(callable $comparator = null): CollectionInterface
    {
        $items = $this->all();
        uksort($items, $comparator ?? Helper::getStringComparator());

        return new self($items);
    }

    /**
     * @inheritDoc
     */
    public function reIndex(): CollectionInterface
    {
        return new self($this->values());
    }

    /**
     * @inheritDoc
     */
    public function chunk(int $size, callable $fn): CollectionInterface
    {
        $deferred = function () use ($size, $fn) {
            foreach (array_chunk($this->all(), $size, true) as $chunk) {
                foreach ($chunk as $index => $value) {
                    yield $index => $fn($value, $index);
                }
            }
        };

        return self::later($deferred());
    }

    /**
     * @inheritDoc
     */
    public function unset(...$offsets): CollectionInterface
    {
        $items = $this->all();
        foreach ($offsets as $offset) {
            unset($items[$offset]);
        }

        return new self($items);
    }

    /**
     * @inheritDoc
     */
    public function set($offset, $item): CollectionInterface
    {
        $added = $this->all();
        $added[$offset] = $item;

        return new self($added);
    }

    /**
     * @inheritDoc
     */
    final public function push($item): CollectionInterface
    {
        $pushed = $this->all();
        $pushed[] = $item;

        return new self($pushed);
    }

    /**
     * @inheritDoc
     */
    final public function unshift(...$items): CollectionInterface
    {
        $unshifted = $this->all();
        array_unshift($unshifted, ...$items);

        return new self($unshifted);
    }
}
