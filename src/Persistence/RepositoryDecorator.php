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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\ProxyGenerator;

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
    /** @var ObjectRepository<T> */
    private ObjectRepository $inner;

    /**
     * @internal
     *
     * @param class-string<T> $class
     */
    public function __construct(private ObjectManager $om, string $class)
    {
        $this->inner = $om->getRepository($class);
    }

    public function assert(): RepositoryAssertions
    {
        return new RepositoryAssertions($this);
    }

    /**
     * @return (T&Proxy)|null
     */
    public function first(string $sortBy = 'id'): ?object
    {
        return $this->findBy([], [$sortBy => 'ASC'], 1)[0] ?? null;
    }

    /**
     * @return (T&Proxy)|null
     */
    public function last(string $sortedField = 'id'): ?object
    {
        return $this->findBy([], [$sortedField => 'DESC'], 1)[0] ?? null;
    }

    /**
     * @return (T&Proxy)|null
     */
    public function find($id): ?object
    {
        if (\is_array($id) && !\array_is_list($id)) {
            return $this->findOneBy($id);
        }

        return self::wrap($this->inner->find($id));
    }

    /**
     * @return list<T&Proxy>
     */
    public function findAll(): array
    {
        return \array_map(ProxyGenerator::wrap(...), $this->inner->findAll()); // @phpstan-ignore-line
    }

    /**
     * @param ?int $limit
     * @param ?int $offset
     *
     * @return list<T&Proxy>
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return \array_map(ProxyGenerator::wrap(...), $this->inner->findBy($criteria, $orderBy, $limit, $offset)); // @phpstan-ignore-line
    }

    /**
     * @return (T&Proxy)|null
     */
    public function findOneBy(array $criteria): ?object
    {
        return self::wrap($this->inner->findOneBy($criteria));
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

    public function truncate(): void
    {
        if ($this->om instanceof EntityManagerInterface) {
            $this->om->createQuery("DELETE {$this->getClassName()} e")->execute();

            return;
        }

        if ($this->om instanceof DocumentManager) {
            $this->om->getDocumentCollection($this->getClassName())->deleteMany([]);
        }
    }

    /**
     * @param ?T $object
     *
     * @return (T&Proxy)|null
     */
    private static function wrap(?object $object): ?object
    {
        return $object ? ProxyGenerator::wrap($object) : null;
    }
}
