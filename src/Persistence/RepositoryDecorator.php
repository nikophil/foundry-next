<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Persistence;


use Doctrine\Persistence\ObjectRepository as I;
use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @template I of I
 * @implements I<T>
 * @mixin I
 *
 * @phpstan-import-type Parameters from Factory
 */
interface RepositoryDecorator extends \Countable
{
    public function assert(): RepositoryAssertions;

    /**
     * @return T|null
     */
    public function first(string $sortBy = 'id'): ?object;

    /**
     * @return T
     */
    public function firstOrFail(string $sortBy = 'id'): object;

    /**
     * @return T|null
     */
    public function last(string $sortedField = 'id'): ?object;

    /**
     * @return T
     */
    public function lastOrFail(string $sortBy = 'id'): object;

    /**
     * @return T|null
     */
    public function find($id): ?object;

    /**
     * @return T
     */
    public function findOrFail(mixed $id): object;

    /**
     * @return T[]
     */
    public function findAll(): array;

    /**
     * @param ?int $limit
     * @param ?int $offset
     *
     * @return T[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array;

    /**
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object;

    public function getClassName(): string;

    /**
     * @param Parameters $criteria
     */
    public function count(array $criteria = []): int;

    public function truncate(): void;

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public function random(array $criteria = []): object;

    /**
     * @param positive-int $count
     * @param Parameters $criteria
     *
     * @return T[]
     */
    public function randomSet(int $count, array $criteria = []): array;

    /**
     * @param positive-int $min
     * @param positive-int $max
     * @param Parameters $criteria
     *
     * @return T[]
     */
    public function randomRange(int $min, int $max, array $criteria = []): array;
}
