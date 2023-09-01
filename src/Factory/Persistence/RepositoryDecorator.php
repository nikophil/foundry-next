<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Persistence;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Zenstruck\Foundry\Factory;

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
     * @param ObjectRepository<T> $inner
     */
    public function __construct(private ObjectRepository $inner)
    {
    }

    public function assert(): RepositoryAssertions
    {
        return new RepositoryAssertions($this);
    }

    public function find($id): ?object
    {
        return $this->inner->find($id);
    }

    public function findAll(): array
    {
        return $this->inner->findAll();
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->inner->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?object
    {
        return $this->inner->findOneBy($criteria);
    }

    public function getClassName(): string
    {
        return $this->inner->getClassName();
    }

    /**
     * @param Parameters $criteria
     */
    public function count(array $criteria = []): int
    {
        if ($this->inner instanceof EntityRepository) {
            // use query to avoid loading all entities
            return $this->inner->count($criteria);
        }

        return \count($this->findBy($criteria));
    }
}
