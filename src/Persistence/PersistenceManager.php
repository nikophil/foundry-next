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
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Exception\PersistenceNotAvailable;
use Zenstruck\Foundry\ORM\ORMPersistenceStrategy;
use Zenstruck\Foundry\Tests\Fixture\TestKernel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class PersistenceManager
{
    private static bool $hasDatabaseBeenReset = false;
    private static bool $ormOnly = false;

    private bool $flush = true;

    /**
     * @param PersistenceStrategy[] $strategies
     */
    public function __construct(private iterable $strategies)
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
        $strategyClasses = [];

        try {
            $strategies = $configuration->persistence()->strategies;
        } catch (PersistenceNotAvailable $e) {
            if (!\class_exists(TestKernel::class)) {
                throw $e;
            }

            // allow this to fail if running foundry test suite
            return;
        }

        foreach ($strategies as $strategy) {
            $strategy->resetDatabase($kernel);
            $strategyClasses[] = $strategy::class;
        }

        if ([ORMPersistenceStrategy::class] === $strategyClasses) {
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

        try {
            $strategies = $configuration->persistence()->strategies;
        } catch (PersistenceNotAvailable $e) {
            if (!\class_exists(TestKernel::class)) {
                throw $e;
            }

            // allow this to fail if running foundry test suite
            return;
        }

        foreach ($strategies as $strategy) {
            $strategy->resetSchema($kernel);
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
        $om = $this->strategyFor($object::class)->objectManagerFor($object::class);
        $om->persist($object);
        $this->flush($om);

        return $object;
    }

    /**
     * @param callable():void $callback
     */
    public function flushAfter(callable $callback): void
    {
        $this->flush = false;

        $callback();

        $this->flush = true;

        foreach ($this->strategies as $strategy) {
            foreach ($strategy->objectManagers() as $om) {
                $this->flush($om);
            }
        }
    }

    public function flush(ObjectManager $om): void
    {
        if ($this->flush) {
            $om->flush();
        }
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
        $strategy = $this->strategyFor($object::class);

        if ($strategy->hasChanges($object)) {
            throw new \RuntimeException(\sprintf('Cannot auto refresh "%s" as there are unsaved changes. Be sure to call ->_save() or disable auto refreshing (see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#auto-refresh for details).', $object::class));
        }

        $om = $strategy->objectManagerFor($object::class);

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
        $om = $this->strategyFor($object::class)->objectManagerFor($object::class);
        $om->remove($object);
        $this->flush($om);

        return $object;
    }

    /**
     * @param class-string $class
     */
    public function truncate(string $class): void
    {
        $this->strategyFor($class)->truncate($class);
    }

    /**
     * @param class-string $class
     */
    public function autoPersist(string $class): bool
    {
        return $this->strategyFor($class)->autoPersist();
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
        return $this->strategyFor($class)->objectManagerFor($class)->getRepository($class);
    }

    /**
     * @param class-string $parent
     * @param class-string $child
     */
    public function relationshipMetadata(string $parent, string $child): ?RelationshipMetadata
    {
        return $this->strategyFor($parent)->relationshipMetadata($parent, $child);
    }

    private static function canSkipSchemaReset(): bool
    {
        return self::$ormOnly && self::isDAMADoctrineTestBundleEnabled();
    }

    /**
     * @param class-string $class
     */
    private function strategyFor(string $class): PersistenceStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($class)) {
                return $strategy;
            }
        }

        throw new \LogicException(\sprintf('No persistence strategy found for "%s".', $class));
    }
}
