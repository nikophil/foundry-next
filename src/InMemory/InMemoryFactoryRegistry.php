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
        private readonly InMemoryManager $inMemoryManager,
    ) {
    }

    public function get(string $class): Factory
    {
        $factory = $this->decorated->get($class);

        $configuration = Configuration::instance();

        if (
            !$configuration->isInMemoryAvailable()
            || !$configuration->inMemory()->isInMemoryEnabled()
            || !$factory instanceof PersistentObjectFactory
        ) {
            return $factory;
        }

        $factory = $factory->withoutPersisting();

        if ($inMemoryRepository = $this->inMemoryManager->getInMemoryRepository($class)) {
            $factory = $factory->afterInstantiate(
                static fn(object $object) => $inMemoryRepository->_save($object)
            );
        }

        return $factory->withoutPersisting();
    }
}
