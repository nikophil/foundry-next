<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Persistence;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Persistence\Exception\NotEnoughObjects;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @implements ObjectRepository<T>
 *
 * @phpstan-import-type Parameters from Factory
 */
final class RepositoryDecorator implements ObjectRepository, \Countable
{
    /**
     * @internal
     *
     * @param class-string<T> $class
     */
    public function __construct(private string $class)
    {
    }

    public function assert(): RepositoryAssertions
    {
        return new RepositoryAssertions($this);
    }

    /**
     * @return T|null
     */
    public function first(string $sortBy = 'id'): ?object
    {
        return $this->findBy([], [$sortBy => 'ASC'], 1)[0] ?? null;
    }

    /**
     * @return T|null
     */
    public function last(string $sortedField = 'id'): ?object
    {
        return $this->findBy([], [$sortedField => 'DESC'], 1)[0] ?? null;
    }

    /**
     * @return T|null
     */
    public function find($id): ?object
    {
        if (\is_array($id) && !\array_is_list($id)) {
            return $this->findOneBy($id);
        }

        return $this->inner()->find($id);
    }

    /**
     * @return T[]
     */
    public function findAll(): array
    {
        return $this->inner()->findAll();
    }

    /**
     * @param ?int $limit
     * @param ?int $offset
     *
     * @return T[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->inner()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->inner()->findOneBy($criteria);
    }

    public function getClassName(): string
    {
        return $this->class;
    }

    /**
     * @param Parameters $criteria
     */
    public function count(array $criteria = []): int
    {
        $inner = $this->inner();

        if ($inner instanceof EntityRepository) {
            // use query to avoid loading all entities
            return $inner->count($criteria);
        }

        return \count($this->findBy($criteria));
    }

    public function truncate(): void
    {
        Configuration::instance()->persistence()->truncate($this->class);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public function random(array $criteria = []): object
    {
        return $this->randomSet(1, $criteria)[0];
    }

    /**
     * @param positive-int $count
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public function randomSet(int $count, array $criteria = []): array
    {
        if ($count < 1) {
            throw new \InvalidArgumentException(\sprintf('$number must be positive (%d given).', $count));
        }

        return $this->randomRange($count, $count, $criteria);
    }

    /**
     * @param positive-int $min
     * @param positive-int $max
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public function randomRange(int $min, int $max, array $criteria = []): array
    {
        if ($min < 1) {
            throw new \InvalidArgumentException(\sprintf('$min must be positive (%d given).', $min));
        }

        if ($max < $min) {
            throw new \InvalidArgumentException(\sprintf('$max (%d) cannot be less than $min (%d).', $max, $min));
        }

        $all = \array_values($this->findBy($criteria));

        \shuffle($all);

        if (\count($all) < $max) {
            throw new NotEnoughObjects(\sprintf('At least %d "%s" object(s) must have been persisted (%d persisted).', $max, $this->getClassName(), \count($all)));
        }

        return \array_slice($all, 0, \random_int($min, $max)); // @phpstan-ignore-line
    }

    /**
     * @return ObjectRepository<T>
     */
    private function inner(): ObjectRepository
    {
        return Configuration::instance()->persistence()->repositoryFor($this->class);
    }
}
