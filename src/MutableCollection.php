<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection;

final class MutableCollection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    final public function map(callable $fn): CollectionInterface
    {
        $this->items = array_map($fn, $this->all());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function filter(callable $fn): CollectionInterface
    {
        $this->items = array_filter($this->all(), $fn, ARRAY_FILTER_USE_BOTH);

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function each(callable $fn): CollectionInterface
    {
        foreach ($this->all() as $key => $item) {
            $this->items[$key] = $fn($item, $key);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function reverse(): CollectionInterface
    {
        $this->items = array_reverse($this->all());
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function unique(): CollectionInterface
    {
        $this->items = array_unique($this->all());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function diff(CollectionInterface $collection, callable $comparator = null): CollectionInterface
    {
        $this->items = array_udiff($this->all(), $collection->all(), $comparator ?? Helper::getObjectSafeComparator());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function merge(CollectionInterface $collection): CollectionInterface
    {
        $this->items = array_merge($this->all(), $collection->all());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function intersect(CollectionInterface $collection): CollectionInterface
    {
        $this->items = array_intersect($this->all(), $collection->all());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function sort(callable $comparator = null): CollectionInterface
    {
        uasort($this->items, $comparator ?? Helper::getObjectSafeComparator());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function kSort(callable $comparator = null): CollectionInterface
    {
        uksort($this->items, $comparator ?? Helper::getStringComparator());

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function reIndex(): CollectionInterface
    {
        $this->items = $this->values();
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function chunk(int $size, callable $fn): CollectionInterface
    {
        foreach (array_chunk($this->all(), $size, true) as $chunk) {
            foreach ($chunk as $index => $value) {
                $this->items[$index] = $fn($value, $index);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function unset(...$offsets): CollectionInterface
    {
        foreach ($offsets as $offset) {
            unset($this->items[$offset]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function set($offset, $item): CollectionInterface
    {
        $this->items[$offset] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function push($item): CollectionInterface
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function unshift(...$items): CollectionInterface
    {
        array_unshift($this->items, ...$items);

        return $this;
    }
}
