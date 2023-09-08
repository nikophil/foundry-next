<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory\Persistence\ORM\ORMPersistenceManager;
use Zenstruck\Foundry\Factory\Persistence\PersistenceManagerRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait ResetDatabase
{
    /**
     * @internal
     * @beforeClass
     */
    public static function _resetDatabase(): void
    {
        if (!\is_subclass_of(static::class, KernelTestCase::class)) {
            throw new \RuntimeException(\sprintf('The "%s" trait can only be used on TestCases that extend "%s".', __TRAIT__, KernelTestCase::class));
        }

        if (PersistenceManagerRegistry::$hasDatabaseBeenReset) {
            return;
        }

        if ($isDAMADoctrineTestBundleEnabled = (\class_exists(ORMPersistenceManager::class) && ORMPersistenceManager::isDAMADoctrineTestBundleEnabled())) {
            // disable static connections for this operation
            // :warning: the kernel should not be booted before calling this!
            StaticDriver::setKeepStaticConnections(false);
        }

        $kernel = static::bootKernel();
        $configuration = Configuration::instance();

        if ($configuration->isPersistenceEnabled()) {
            foreach ($configuration->persistence()->managers() as $manager) {
                $manager->resetDatabase($kernel);
            }
        }

        if ($isDAMADoctrineTestBundleEnabled) {
            // re-enable static connections
            StaticDriver::setKeepStaticConnections(true);
        }

        static::ensureKernelShutdown();
        PersistenceManagerRegistry::$hasDatabaseBeenReset = true;
    }

    /**
     * @internal
     * @before
     */
    public static function _resetSchema(): void
    {
        if (!\is_subclass_of(static::class, KernelTestCase::class)) {
            throw new \RuntimeException(\sprintf('The "%s" trait can only be used on TestCases that extend "%s".', __TRAIT__, KernelTestCase::class));
        }

        $kernel = static::bootKernel();
        $configuration = Configuration::instance();

        foreach ($configuration->persistence()->managers() as $manager) {
            $manager->resetSchema($kernel);
        }

        $configuration->stories->loadGlobalStories();

        static::ensureKernelShutdown();
    }
}
