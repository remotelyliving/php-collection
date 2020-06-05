<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection\Tests\Unit;

use RemotelyLiving\PHPCollection\MutableCollection;

class MutableCollectionTest extends AbstractTestCase
{
    public function testIsTraversable(): void
    {
        $collection = MutableCollection::collect([]);
        $this->assertInstanceOf(\Traversable::class, $collection);
        $this->assertIsIterable($collection);
    }

    public function testSortsItemKeysByInteger(): void
    {
        $list = [2 => 3, 1 => 2, 0 => 1];
        $expected = [1, 2, 3];
        $collection = MutableCollection::collect($list);

        $this->assertEquals($expected, $collection->kSort()->values());
        $this->assertEquals($list, $collection->all());
    }

    public function testSortsItemKeysByString(): void
    {
        $list = ['c' => 'baz', 'a' => 'foo', 'b' => 'bar'];
        $expected = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($list);

        $this->assertEquals($expected, $collection->kSort()->values());
        $this->assertEquals($list, $collection->all());
    }

    public function testSortsItems(): void
    {
        $object1 = new \stdClass();
        $object1->foo = 'a';

        $object2 = new \stdClass();
        $object2->foo = 'g';

        $object3 = new \stdClass();
        $object3->foo = 'z';

        $integerList = [3, 4, 5, 1, 2];
        $stringList = ['a', 'z', 'b', 'd'];
        $objectList = ['foo' => $object3, 'bar' => $object2, 'baz' => $object1];

        $expectedIntegerList = [1, 2, 3, 4, 5];
        $expectedStringList = ['a', 'b', 'd', 'z'];
        $expectedObjectList = ['baz' => $object1, 'bar' => $object2, 'foo' => $object3];

        $this->assertSame($expectedObjectList, MutableCollection::collect($objectList)->sort()->all());
        $this->assertEquals($expectedStringList, MutableCollection::collect($stringList)->sort()->values());
        $this->assertEquals($expectedIntegerList, MutableCollection::collect($integerList)->sort()->values());
    }

    public function testTraversesWithoutNeedingToRewind(): void
    {
        $expected = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($expected);
        $actualRun1 = [];
        $actualRun2 = [];

        foreach ($collection as $key => $item) {
            $actualRun1[$key] = $item;
        }
        foreach ($collection as $key => $item) {
            $actualRun2[$key] = $item;
        }

        $this->assertEquals($expected, $actualRun1);
        $this->assertEquals($expected, $actualRun2);
    }

    public function testIsCountable(): void
    {
        $list = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($list);

        $this->assertInstanceOf(\Countable::class, $collection);
        $this->assertSame(count($list), $collection->count());
        $this->assertSame(0, MutableCollection::collect([])->count());
    }

    public function testIsSerializeable(): void
    {
        $list = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($list);

        $this->assertInstanceOf(\Serializable::class, $collection);
        $this->assertEquals($collection, \unserialize(\serialize($collection)));
    }

    public function testIsJsonSerializeable(): void
    {
        $list = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($list);

        $this->assertInstanceOf(\JsonSerializable::class, $collection);
        $this->assertEquals(
            $list,
            \json_decode(\json_encode($collection), true)
        );
    }

    public function testMaps(): void
    {
        $list = ['foo', 'bar', 'baz'];
        $expected = ['foo:mapped', 'bar:mapped', 'baz:mapped'];
        $collection = MutableCollection::collect($list);
        $mapped = $collection->map(fn(string $val) => $val . ':mapped');

        $this->assertEquals($expected, $mapped->all());
    }

    public function testFilters(): void
    {
        $list = ['foo', 1, 'bar', 3, 'baz', 3];
        $expected = ['foo', 'bar', 'baz'];
        $collection = MutableCollection::collect($list);
        $filtered = $collection->filter(fn($val) => is_string($val));

        $this->assertEquals($expected, $filtered->values());
    }

    public function testRekeysTheCollection(): void
    {
        $expected = [1, 5, 10, 100, 123, 32, 0, -1];
        $list = [1 => 1, 10 => 5, 11 => 10, 3 => 100, 5 => 123, 6 => 32, 7 => 0, -2 => -1];
        $collection = MutableCollection::collect($list);
        $rekeyed = $collection->reIndex();

        $this->assertSame($expected, $rekeyed->all());
    }

    public function testChunks(): void
    {
        $expected = ['a:0', 'b:1', 'c:2', 'd:3', 'e:4','f:5', 'g:6'];
        $list = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $collection = MutableCollection::collect($list);
        $chunked = $collection->chunk(
            2,
            function (string $val, int $index): string {
                return "{$val}:{$index}";
            }
        );

        $this->assertSame($expected, $chunked->all());
    }

    public function testIterates(): void
    {
        $list = [3, 2, 1];
        $expected = [0 => 0, 1 => 2, 2 => 2];
        $collection = MutableCollection::collect($list);
        $iterated = $collection->each(fn(int $val, int $key) => $val * $key);

        $this->assertEquals($expected, $iterated->all());
    }

    public function testReverses(): void
    {
        $list = [3, 2, 1];
        $expected = [1, 2, 3];
        $collection = MutableCollection::collect($list);
        $reversed = $collection->reverse();

        $this->assertEquals($expected, $reversed->all());
    }

    public function testGetsFirstItem(): void
    {
        $list = [3, 2, 1];
        $this->assertSame(3, MutableCollection::collect($list)->first());
        $this->assertNull(MutableCollection::collect([])->first());
    }

    public function testGetsLastItem(): void
    {
        $list = [3, 2, 1];
        $this->assertSame(1, MutableCollection::collect($list)->last());
        $this->assertNull(MutableCollection::collect([])->last());
    }

    public function testReduces(): void
    {
        $list = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $collection = MutableCollection::collect($list);
        $reduced = $collection->reduce(fn(int $val, int $carry) => $val * $carry, 1);
        
        $this->assertSame(3628800, $reduced);
    }

    public function testUniques(): void
    {
        $list = [10, 1, 2, 3, 4, 10, 5, 6, 7, 8, 9, 10];
        $collection = MutableCollection::collect($list);
        $unique = $collection->unique();
        
        $this->assertEquals([10, 1, 2, 3, 4, 5, 6, 7, 8, 9], $unique->values());
    }

    public function testDiffs(): void
    {
        $list1 = ['a', 'b', 'c'];
        $list2 = ['b', 'c', 'd'];
        $collection1 = MutableCollection::collect($list1);
        $collection2 = MutableCollection::collect($list2);

        $this->assertEquals(['a'], $collection1->diff($collection2)->values());
        $this->assertEquals([], $collection1->diff($collection1)->values());
        $this->assertEquals([], $collection2->diff($collection2)->values());
    }

    public function testMerges(): void
    {
        $list1 = ['a', 'b', 'c'];
        $list2 = ['b', 'c', 'd'];
        $collection1 = MutableCollection::collect($list1);
        $collection2 = MutableCollection::collect($list2);

        $this->assertEquals(['a', 'b', 'c', 'b', 'c', 'd'], $collection1->merge($collection2)->all());
    }

    public function testUnions(): void
    {
        $list1 = ['a', 'b', 'c'];
        $list2 = ['b', 'c', 'd'];
        $collection1 = MutableCollection::collect($list1);
        $collection2 = MutableCollection::collect($list2);

        $this->assertEquals(['a', 'b', 'c', 'd'], $collection1->union($collection2)->values());
    }

    public function testIntersects(): void
    {
        $list1 = ['a', 'b', 'c'];
        $list2 = ['b', 'c', 'd'];
        $collection1 = MutableCollection::collect($list1);
        $collection2 = MutableCollection::collect($list2);

        $this->assertEquals(['b', 'c'], $collection1->intersect($collection2)->values());
    }

    public function testKnowsIfEmpty(): void
    {
        $this->assertTrue(MutableCollection::collect([])->empty());

        $this->assertFalse(MutableCollection::collect([null])->empty());
        $this->assertFalse(MutableCollection::collect([0])->empty());
        $this->assertFalse(MutableCollection::collect([''])->empty());
        $this->assertFalse(MutableCollection::collect([true])->empty());
        $this->assertFalse(MutableCollection::collect(['hey hey'])->empty());
    }

    public function testGetsAll(): void
    {
        $this->assertSame([], MutableCollection::collect([])->all());
        $this->assertSame([1, 2, 3], MutableCollection::collect([1, 2, 3])->all());
    }

    public function testGetsAllWithoutKeys(): void
    {
        $this->assertSame([], MutableCollection::collect([])->all());
        $this->assertSame([1, 2, 3], MutableCollection::collect([123 => 1, 'b' => 2, 321 => 3])->values());
    }

    public function testKnowsIfEqualsOtherCollection(): void
    {
        $list1 = ['a' => new \stdClass(), 'b' => new \stdClass(), 'c' => new \stdClass()];
        $list2 = ['b' => 1, 'c' => 2, 'd' => 3];

        $this->assertTrue(MutableCollection::collect($list1)->equals(MutableCollection::collect($list1)));
        $this->assertTrue(MutableCollection::collect($list2)->equals(MutableCollection::collect($list2)));
        $this->assertFalse(MutableCollection::collect($list1)->equals(MutableCollection::collect($list2)));
        $this->assertFalse(MutableCollection::collect($list2)->equals(MutableCollection::collect($list1)));
    }

    public function testKnowsIfHas(): void
    {
        $list = ['a' => new \stdClass(), 'b' => new \stdClass(), 'c' => new \stdClass(), 23 => new \stdClass()];
        $collection = MutableCollection::collect($list);

        $this->assertTrue($collection->has('a'));
        $this->assertFalse($collection->has('d'));

        $this->assertTrue($collection->has(23));
        $this->assertFalse($collection->has(32));
    }

    public function testKnowsIfHasAnItem(): void
    {
        $list = ['a' => new \stdClass(), 'b' => new \stdClass(), 'c' => new \stdClass(), 23 => new \stdClass()];
        $collection = MutableCollection::collect($list);

        $this->assertTrue($collection->has('a'));
        $this->assertFalse($collection->has('d'));

        $this->assertTrue($collection->has(23));
        $this->assertFalse($collection->has(32));
    }

    public function testKnowsIfContainsAnItem(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $list = ['a', 'b', 'c', 'd', $object];
        $collection = MutableCollection::collect($list);

        $this->assertTrue($collection->contains('a'));
        $this->assertTrue($collection->contains($object));
        $this->assertFalse($collection->contains(new \stdClass()));
    }


    public function testKnowsIfAtLeasOneItemsMeetsACriteria(): void
    {
        $list = ['a', 'b', 'c', new \stdClass(), 'd'];
        $collection = MutableCollection::collect($list);

        $this->assertTrue($collection->some(fn($val) => is_object($val)));
        $this->assertTrue($collection->some(fn($val, $key) => $key > 1));
        $this->assertFalse($collection->some(fn($val) => is_array($val)));
    }

    public function testGetsAnItemAndReturnsDefaultIfNotFound(): void
    {
        $list = ['a', 'b', 'c', 'd'];
        $collection = MutableCollection::collect($list);

        $this->assertSame('c', $collection->get(2));
        $this->assertNull($collection->get('foo'));
        $this->assertSame('hey hey', $collection->get('foo', 'hey hey'));
    }

    public function testGetsRandomItemFromCollection(): void
    {
        $list = ['a', 'b', 'c', 'd'];
        $collection = MutableCollection::collect($list);

        $this->assertContains($collection->rand(), $list);
    }

    public function testRemovesItemsFromCollection(): void
    {
        $list = ['a', 'b', 'c', 'd'];
        $collection = MutableCollection::collect($list);
        $removed = $collection->unset(0, 3);

        $this->assertEquals(['b', 'c'], $removed->values());
        $this->assertEquals(['b', 'c'], $collection->values());
    }

    public function testAddsItemsToCollection(): void
    {
        $list = ['bar' => 'baz'];
        $collection = MutableCollection::collect($list);

        $this->assertEquals(['bar' => 'baz', 'foo' => 'bar'], $collection->set('foo', 'bar')->all());
    }

    public function testUnshiftsItemsToCollection(): void
    {
        $list = ['c', 'd'];
        $collection = MutableCollection::collect($list);

        $this->assertEquals(['a', 'b', 'c', 'd'], $collection->unshift('a', 'b')->all());
    }

    public function testPushesItemOntoCollection(): void
    {
        $list = ['a', 'b', 'c'];
        $collection = MutableCollection::collect($list);

        $this->assertEquals(['a', 'b', 'c', 'd'], $collection->push('d')->all());
    }

    public function testGetsIterator(): void
    {
        $collection = MutableCollection::collect([1, 2, 3]);
        $iterator1 = $collection->getIterator();
        $iterator2 = $collection->getIterator();

        $this->assertEquals($iterator1, $iterator2);
        $this->assertNotSame($iterator1, $iterator2);
    }

    public function testGetsItemsDeferred(): void
    {
        $collection = MutableCollection::collect([1, 2, 3]);
        $generator = $collection->deferred();
        $this->assertEquals($collection->all(), iterator_to_array($generator));
    }

    public function testDisallowsMutatingViaUnserialization(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot unserialize instance of collection');

        MutableCollection::collect([1, 2, 3])->unserialize('');
    }

    public function testFactoryMethods(): void
    {
        $list = [1, 2, 3];
        $generator = function () {
            yield 1;
            yield 2;
            yield 3;
        };

        $collected = MutableCollection::collect($list);
        $fromGenerator = MutableCollection::later($generator());
        $exploded = MutableCollection::fromString('1,2,3');
        $filled = MutableCollection::fill(0, 3, -1);

        $this->assertEquals($list, $collected->all());
        $this->assertEquals($list, $fromGenerator->all());
        $this->assertEquals($list, $exploded->all());
        $this->assertEquals([-1, -1, -1], $filled->all());
    }
}
