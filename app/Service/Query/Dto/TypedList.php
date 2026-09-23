<?php

declare(strict_types=1);

namespace App\Service\Query\Dto;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use OutOfBoundsException;
use Traversable;

/**
 * @template T of object
 *
 * @implements ArrayAccess<int, T>
 * @implements IteratorAggregate<int, T>
 */
abstract readonly class TypedList implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     */
    final public function __construct(protected array $items)
    {
        $itemType = static::itemType();

        foreach ($items as $item) {
            if (! $item instanceof $itemType) {
                throw new InvalidArgumentException("{$itemType}以外の要素は格納できません。");
            }
        }
    }

    final public function count(): int
    {
        return count($this->items);
    }

    final public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @param  callable(T): bool  $predicate
     * @return T|null
     */
    final public function find(callable $predicate): mixed
    {
        foreach ($this->items as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }

        return null;
    }

    /** @return Traversable<int, T> */
    final public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    final public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && isset($this->items[$offset]);
    }

    /** @return T */
    final public function offsetGet(mixed $offset): mixed
    {
        if (! $this->offsetExists($offset)) {
            throw new OutOfBoundsException('存在しないListの位置が指定されました。');
        }

        return $this->items[$offset];
    }

    final public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException('TypedListは変更できません。');
    }

    final public function offsetUnset(mixed $offset): never
    {
        throw new LogicException('TypedListは変更できません。');
    }

    /** @return class-string<T> */
    abstract protected static function itemType(): string;
}
