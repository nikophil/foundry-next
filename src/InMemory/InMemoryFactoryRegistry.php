<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\InMemory;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\FactoryRegistryInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @internal
 */
final class InMemoryFactoryRegistry implements FactoryRegistryInterface
{
    public function __construct(
        private readonly FactoryRegistryInterface $decorated,
        private readonly ServiceLocator $inMemoryRepositories,
    ) {
    }

    public function get(string $class): Factory
    {
        $factory = $this->decorated->get($class);

        $configuration = Configuration::instance();

        if (
            !$configuration->isPersistenceAvailable()
            || !$configuration->persistence()->isInMemoryEnabled()
            || !$factory instanceof PersistentObjectFactory
        ) {
            return $factory;
        }

        $factory = $factory->withoutPersisting();

        if ($inMemoryRepository = $this->findInMemoryRepository($class)) {
            $factory = $factory->afterInstantiate(
                static fn(object $object) => $inMemoryRepository->_save($object)
            );
        }

        return $factory->withoutPersisting();
    }

    /**
     * @param class-string<Factory> $class
     *
     * @return InMemoryRepository<T>|null
     */
    private function findInMemoryRepository(string $class): InMemoryRepository|null
    {
        $targetClass = $class::class();
        if (!$this->inMemoryRepositories->has($targetClass)) {
            // todo: should this behavior be opt-in from bundle's configuration?
            // ie: maybe user will want to throw an exception here.
            // but nullability could help adhesion to the feature
            return null;
        }

        return $this->inMemoryRepositories->get($targetClass);
    }
}
