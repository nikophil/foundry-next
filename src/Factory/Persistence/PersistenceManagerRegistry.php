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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class PersistenceManagerRegistry
{
    public static bool $hasDatabaseBeenReset = false;

    /**
     * @param PersistenceManager[] $managers
     */
    public function __construct(private iterable $managers)
    {
    }

    /**
     * @param class-string $class
     */
    public function managerFor(string $class): PersistenceManager
    {
        foreach ($this->managers as $manager) {
            if ($manager->supports($class)) {
                return $manager;
            }
        }

        throw new \LogicException(\sprintf('No persistence manager found for "%s".', $class));
    }

    /**
     * @param class-string $class
     */
    public function objectManagerFor(string $class): ObjectManager
    {
        return $this->managerFor($class)->objectManagerFor($class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return RepositoryDecorator<T>
     */
    public function repositoryFor(string $class): RepositoryDecorator
    {
        return $this->managerFor($class)->repositoryFor($class);
    }

    /**
     * @return PersistenceManager[]
     */
    public function managers(): iterable
    {
        return $this->managers;
    }
}
