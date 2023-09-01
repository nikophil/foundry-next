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

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainPersistenceManager implements PersistenceManager
{
    /**
     * @param PersistenceManager[] $managers
     */
    public function __construct(private iterable $managers)
    {
    }

    public function supports(string $class): bool
    {
        foreach ($this->managers as $manager) {
            if ($manager->supports($class)) {
                return true;
            }
        }

        return false;
    }

    public function objectManagerFor(string $class): ObjectManager
    {
        foreach ($this->managers as $manager) {
            if ($manager->supports($class)) {
                return $manager->objectManagerFor($class);
            }
        }

        throw new \LogicException(\sprintf('No persistence manager found for "%s".', $class));
    }

    public function repositoryFor(string $class): RepositoryDecorator
    {
        return new RepositoryDecorator($this->objectManagerFor($class)->getRepository($class));
    }

    public function resetDatabase(KernelInterface $kernel): void
    {
        foreach ($this->managers as $manager) {
            $manager->resetDatabase($kernel);
        }
    }

    public function resetSchema(KernelInterface $kernel): void
    {
        foreach ($this->managers as $manager) {
            $manager->resetSchema($kernel);
        }
    }
}
