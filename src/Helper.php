<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPCollection;

final class Helper
{
    public static function getStringComparator(): callable
    {
        return function ($a, $b): int {
            return strcmp((string) $a, (string) $b);
        };
    }

    public static function getObjectSafeComparator(): callable
    {
        return function ($a, $b): int {
            return (\gettype($a) === \gettype($b)) ? $a <=> $b : -1;
        };
    }

    public static function unwrapDeferred(\Generator $deferredValues): array
    {
        $items = [];
        foreach ($deferredValues as $key => $item) {
            $items[$key] = $item;
        }

        return $items;
    }
}
