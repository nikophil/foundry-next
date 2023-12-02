<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\InMemory;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\FactoryRegistryInterface;

final class InMemoryManager
{
    private bool $inMemory = false;

    public function __construct(
        private readonly ServiceLocator $inMemoryRepositories,
    ) {
    }

    public function isInMemoryEnabled(): bool
    {
        return $this->inMemory;
    }

    public function enableInMemory(): void
    {
        Configuration::instance()->persistence()->disablePersisting();
        $this->inMemory = true;
    }

    /**
     * @param class-string<Factory> $class
     *
     * @return InMemoryRepository<T>|null
     */
    public function getInMemoryRepository(string $class): InMemoryRepository|null
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
