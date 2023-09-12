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

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\ORM\ORMPersistenceManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class PersistenceManagerRegistry
{
    private static bool $hasDatabaseBeenReset = false;
    private static bool $ormOnly = false;

    /**
     * @param PersistenceManager[] $managers
     */
    public function __construct(private iterable $managers)
    {
    }

    public static function isDAMADoctrineTestBundleEnabled(): bool
    {
        return \class_exists(StaticDriver::class) && StaticDriver::isKeepStaticConnections();
    }

    /**
     * @param callable():KernelInterface $createKernel
     * @param callable():void            $shutdownKernel
     */
    public static function resetDatabase(callable $createKernel, callable $shutdownKernel): void
    {
        if (self::$hasDatabaseBeenReset) {
            return;
        }

        if ($isDAMADoctrineTestBundleEnabled = self::isDAMADoctrineTestBundleEnabled()) {
            // disable static connections for this operation
            // :warning: the kernel should not be booted before calling this!
            StaticDriver::setKeepStaticConnections(false);
        }

        $kernel = $createKernel();
        $configuration = Configuration::instance();
        $managerClasses = [];

        foreach ($configuration->persistence()->managers as $manager) {
            $manager->resetDatabase($kernel);
            $managerClasses[] = $manager::class;
        }

        if ([ORMPersistenceManager::class] === $managerClasses) {
            // enable skipping booting the kernel for resetSchema()
            self::$ormOnly = true;
        }

        if ($isDAMADoctrineTestBundleEnabled && self::$ormOnly) {
            // add global stories so they are available after transaction rollback
            $configuration->stories->loadGlobalStories();
        }

        if ($isDAMADoctrineTestBundleEnabled) {
            // re-enable static connections
            StaticDriver::setKeepStaticConnections(true);
        }

        $shutdownKernel();

        self::$hasDatabaseBeenReset = true;
    }

    /**
     * @param callable():KernelInterface $createKernel
     * @param callable():void            $shutdownKernel
     */
    public static function resetSchema(callable $createKernel, callable $shutdownKernel): void
    {
        if (self::canSkipSchemaReset()) {
            // can fully skip booting the kernel
            return;
        }

        $kernel = $createKernel();
        $configuration = Configuration::instance();

        foreach ($configuration->persistence()->managers as $manager) {
            $manager->resetSchema($kernel);
        }

        $configuration->stories->loadGlobalStories();

        $shutdownKernel();
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function save(object $object): object
    {
        $om = $this->managerFor($object::class)->objectManagerFor($object::class);
        $om->persist($object);
        $om->flush();

        return $object;
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function refresh(object &$object): object
    {
        $manager = $this->managerFor($object::class);

        if ($manager->hasChanges($object)) {
            throw new \RuntimeException(\sprintf('Cannot auto refresh "%s" as there are unsaved changes. Be sure to call ->_save() or disable auto refreshing (see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#auto-refresh for details).', $object::class));
        }

        $om = $manager->objectManagerFor($object::class);

        if ($om->contains($object)) {
            $om->refresh($object);

            return $object;
        }

        $id = $om->getClassMetadata($object::class)->getIdentifierValues($object);

        if (!$id || !$object = $om->find($object::class, $id)) {
            throw new \RuntimeException('object no longer exists...');
        }

        return $object;
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function delete(object $object): object
    {
        $om = $this->managerFor($object::class)->objectManagerFor($object::class);
        $om->remove($object);
        $om->flush();

        return $object;
    }

    /**
     * @param class-string $class
     */
    public function truncate(string $class): void
    {
        $this->managerFor($class)->truncate($class);
    }

    /**
     * @param class-string $class
     */
    public function autoPersist(string $class): bool
    {
        return $this->managerFor($class)->autoPersist();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ObjectRepository<T>
     */
    public function repositoryFor(string $class): ObjectRepository
    {
        return $this->managerFor($class)->objectManagerFor($class)->getRepository($class);
    }

    private static function canSkipSchemaReset(): bool
    {
        return self::$ormOnly && self::isDAMADoctrineTestBundleEnabled();
    }

    /**
     * @param class-string $class
     */
    private function managerFor(string $class): PersistenceManager
    {
        foreach ($this->managers as $manager) {
            if ($manager->supports($class)) {
                return $manager;
            }
        }

        throw new \LogicException(\sprintf('No persistence manager found for "%s".', $class));
    }
}
