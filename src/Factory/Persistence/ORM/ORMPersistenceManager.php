<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Persistence\ORM;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Zenstruck\Foundry\Factory\Persistence\PersistenceManager;
use Zenstruck\Foundry\Factory\Persistence\RepositoryDecorator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ORMPersistenceManager implements PersistenceManager
{
    /**
     * @param array{
     *     auto_persist: bool,
     *     auto_refresh: bool,
     *     reset: array{connections: string[], entity_managers: string[]}
     * } $config
     */
    public function __construct(private ManagerRegistry $registry, private array $config)
    {
    }

    public static function isDAMADoctrineTestBundleEnabled(): bool
    {
        return \class_exists(StaticDriver::class) && StaticDriver::isKeepStaticConnections();
    }

    public function autoPersist(): bool
    {
        return $this->config['auto_persist'];
    }

    public function autoRefresh(): bool
    {
        return $this->config['auto_persist'];
    }

    public function hasChanges(object $object): bool
    {
        $em = $this->objectManagerFor($object::class);

        if (!$em->contains($object)) {
            return false;
        }

        // cannot use UOW::recomputeSingleEntityChangeSet() here as it wrongly computes embedded objects as changed
        $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata($object::class), $object);

        return (bool) $em->getUnitOfWork()->getEntityChangeSet($object);
    }

    public function supports(string $class): bool
    {
        return (bool) $this->registry->getManagerForClass($class);
    }

    public function objectManagerFor(string $class): EntityManagerInterface
    {
        return $this->registry->getManagerForClass($class) ?? throw new \LogicException(\sprintf('No manager found for "%s".', $class)); // @phpstan-ignore-line
    }

    public function repositoryFor(string $class): RepositoryDecorator
    {
        return new RepositoryDecorator($this->objectManagerFor($class)->getRepository($class));
    }

    public function resetDatabase(KernelInterface $kernel): void
    {
        $application = self::application($kernel);

        foreach ($this->connections() as $connection) {
            $databasePlatform = $this->registry->getConnection($connection)->getDatabasePlatform(); // @phpstan-ignore-line

            if ($databasePlatform instanceof PostgreSQLPlatform) {
                // let's drop all connections to the database to be able to drop it
                $this->runCommand(
                    $application,
                    'dbal:run-sql',
                    [
                        '--connection' => $connection,
                        'sql' => 'SELECT pid, pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = current_database() AND pid <> pg_backend_pid()',
                    ],
                    canFail: true,
                );
            }

            $dropParams = ['--connection' => $connection, '--force' => true];

            if (!$databasePlatform instanceof SqlitePlatform) {
                // sqlite does not support "--if-exists" (ref: https://github.com/doctrine/dbal/pull/2402)
                $dropParams['--if-exists'] = true;
            }

            $this->runCommand($application, 'doctrine:database:drop', $dropParams);
            $this->runCommand($application, 'doctrine:database:create', ['--connection' => $connection]);
        }

        $this->createSchema($application);
    }

    public function resetSchema(KernelInterface $kernel): void
    {
        if (self::isDAMADoctrineTestBundleEnabled()) {
            // not required as the DAMADoctrineTestBundle wraps each test in a transaction
            return;
        }

        $application = self::application($kernel);

        $this->dropSchema($application);
        $this->createSchema($application);
    }

    private function createSchema(Application $application): void
    {
        // todo migration support

        foreach ($this->managers() as $manager) {
            $this->runCommand($application, 'doctrine:schema:update', [
                '--em' => $manager,
                '--force' => true,
            ]);
        }
    }

    private function dropSchema(Application $application): void
    {
        // todo migration support

        foreach ($this->managers() as $manager) {
            $this->runCommand($application, 'doctrine:schema:drop', [
                '--em' => $manager,
                '--force' => true,
            ]);
        }
    }

    /**
     * @param array<string,scalar> $parameters
     */
    private function runCommand(Application $application, string $command, array $parameters = [], bool $canFail = false): void
    {
        $exit = $application->run(
            new ArrayInput(\array_merge(['command' => $command], $parameters)),
            $output = new BufferedOutput()
        );

        if (0 !== $exit && !$canFail) {
            throw new \RuntimeException(\sprintf('Error running "%s": %s', $command, $output->fetch()));
        }
    }

    private static function application(KernelInterface $kernel): Application
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        return $application;
    }

    /**
     * @return string[]
     */
    private function managers(): array
    {
        return $this->config['reset']['entity_managers'];
    }

    /**
     * @return string[]
     */
    private function connections(): array
    {
        return $this->config['reset']['connections'];
    }
}
