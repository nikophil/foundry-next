<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\InMemory;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Object\Hydrator;
use Zenstruck\Foundry\Persistence\Exception\NotEnoughObjects;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\RepositoryAssertions;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;
use Zenstruck\Foundry\Persistence\RepositoryDecoratorTrait;
use function Zenstruck\Foundry\Persistence\proxy;

final class InMemoryRepositoryDecorator implements RepositoryDecorator
{
    use RepositoryDecoratorTrait;

    /**
     * @param class-string<T> $class
     * @internal
     */
    public function __construct(
        private string $class,
        private InMemoryRepository $decorated
    )
    {
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->decorated->{$name}(...$arguments);
    }

    /**
     * @return T|null
     */
    public function first(string $sortBy = 'id'): ?object
    {
        return $this->sort($this->decorated->_all(), $sortBy)[0] ?? null;
    }

    /**
     * @return T
     */
    public function firstOrFail(string $sortBy = 'id'): object
    {
        return $this->sort($this->decorated->_all(), $sortBy)[0] ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', $this->class));
    }

    /**
     * @return T|null
     */
    public function last(string $sortedField = 'id'): ?object
    {
        return $this->sort($this->decorated->_all(), $sortedField, 'DESC')[0] ?? null;
    }

    /**
     * @return T
     */
    public function lastOrFail(string $sortBy = 'id'): object
    {
        return $this->sort($this->decorated->_all(), $sortBy, 'DESC')[0] ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', $this->class));
    }

    /**
     * @return T|null
     */
    public function find($id): ?object
    {
        if (\is_array($id) && !\array_is_list($id)) {
            return $this->findOneBy($id);
        }

        return $this->findOneBy(['id' => $id]);
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
        return $this->decorated->_all();
    }

    /**
     * @param ?int $limit
     * @param ?int $offset
     *
     * @return T[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $result = $this->decorated->_all();

        $criteria = $this->normalize($criteria);
        foreach ($criteria as $property => $value) {
            $result = $this->filter($result, $property, $value);
        }

        return $result;
    }

    /**
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object
    {
        return $this->findBy($criteria)[0] ?? null;
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
        return \count($this->findBy($criteria));
    }

    public function truncate(): void
    {
        // todo? how?
        throw new \BadMethodCallException();
    }

    private function filter(array $items, string $property, mixed $value): array
    {
        return array_values(
            array_filter(
                $items,
                fn(object $item): bool => $this->getValue($item, $property) === $value
            )
        );
    }

    private function sort(array $items, string $property, string $order = 'ASC'): array
    {
        usort(
            $items,
            fn(object $a, object $b): int => $order === 'DESC'
                ? $this->getValue($a, $property) <=> $this->getValue($b, $property)
                : $this->getValue($b, $property) <=> $this->getValue($a, $property)
        );

        return $items;
    }

    private function getValue(object $item, string $property): mixed
    {
        return Hydrator::get($item, $property);
    }
}
