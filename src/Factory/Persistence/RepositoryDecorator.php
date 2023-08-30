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

use Doctrine\Persistence\ObjectRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @implements ObjectRepository<T>
 */
final class RepositoryDecorator implements ObjectRepository
{
    /**
     * @internal
     *
     * @param ObjectRepository<T> $inner
     */
    public function __construct(private ObjectRepository $inner)
    {
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
}
