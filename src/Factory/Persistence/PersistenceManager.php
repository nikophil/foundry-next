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
 *
 * @internal
 */
interface PersistenceManager
{
    /**
     * @param class-string $class
     */
    public function supports(string $class): bool;

    /**
     * @param class-string $class
     *
     * @throws \LogicException if no manager found for $class
     */
    public function objectManagerFor(string $class): ObjectManager;

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return RepositoryDecorator<T>
     *
     * @throws \LogicException if no repository found for $class
     */
    public function repositoryFor(string $class): RepositoryDecorator;

    public function resetDatabase(KernelInterface $kernel): void;

    public function resetSchema(KernelInterface $kernel): void;
}
