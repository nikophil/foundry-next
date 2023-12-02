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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @template I of ObjectRepository
 * @implements I<T>
 * @mixin I
 *
 * @phpstan-import-type Parameters from Factory
 */
final class PersistenceRepositoryDecorator implements ObjectRepository, RepositoryDecorator
{
    use RepositoryDecoratorTrait;

    /**
     * @internal
     *
     * @param class-string<T> $class
     */
    public function __construct(private string $class)
    {
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->inner()->{$name}(...$arguments);
    }

    /**
     * @return T|null
     */
    public function first(string $sortBy = 'id'): ?object
    {
        return $this->findBy([], [$sortBy => 'ASC'], 1)[0] ?? null;
    }

    /**
     * @return T
     */
    public function firstOrFail(string $sortBy = 'id'): object
    {
        return $this->first($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', $this->class));
    }

    /**
     * @return T|null
     */
    public function last(string $sortedField = 'id'): ?object
    {
        return $this->findBy([], [$sortedField => 'DESC'], 1)[0] ?? null;
    }

    /**
     * @return T
     */
    public function lastOrFail(string $sortBy = 'id'): object
    {
        return $this->last($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', $this->class));
    }

    /**
     * @return T|null
     */
    public function find($id): ?object
    {
        if (\is_array($id) && !\array_is_list($id)) {
            return $this->findOneBy($id);
        }

        return $this->inner()->find(unproxy($id));
    }

    /**
     * @return T
     */
    public function findOrFail(mixed $id): object
    {
        return $this->find($id) ?? throw new \RuntimeException(\sprintf('No "%s" object found for "%s".', $this->class, \get_debug_type($id)));
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
        return $this->inner()->findBy($this->normalize($criteria), $orderBy, $limit, $offset);
    }

    /**
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->inner()->findOneBy($this->normalize($criteria));
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
            return $inner->count($this->normalize($criteria));
        }

        return \count($this->findBy($criteria));
    }

    public function truncate(): void
    {
        Configuration::instance()->persistence()->truncate($this->class);
    }

    /**
     * @return ObjectRepository<T>
     */
    private function inner(): ObjectRepository
    {
        return Configuration::instance()->persistence()->repositoryFor($this->class);
    }
}
