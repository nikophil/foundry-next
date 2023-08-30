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

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class PersistenceManager
{
    private Application $application;

    public function __construct(private ManagerRegistry $registry, private KernelInterface $kernel)
    {
    }

    /**
     * @param class-string $class
     */
    public function objectManagerFor(string $class): ObjectManager
    {
        return $this->registry->getManagerForClass($class) ?? throw new \LogicException(\sprintf('No manager found for "%s".', $class));
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
        return new RepositoryDecorator($this->registry->getRepository($class));
    }

    public function resetDatabase(): void
    {
        foreach ($this->registry->getConnectionNames() as $connection) { // todo customize connections
            $databasePlatform = $this->registry->getConnection($connection)->getDatabasePlatform(); // @phpstan-ignore-line

            if ($databasePlatform instanceof PostgreSQLPlatform) {
                // let's drop all connections to the database to be able to drop it
                $this->runCommand(
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

            $this->runCommand('doctrine:database:drop', $dropParams);
            $this->runCommand('doctrine:database:create', ['--connection' => $connection]);
        }

        $this->createSchema();
    }

    public function resetSchema(): void
    {
        // todo dama support

        $this->dropSchema();
        $this->createSchema();
    }

    private function createSchema(): void
    {
        // todo migration support

        foreach ($this->registry->getManagerNames() as $manager) { // todo customize object managers
            $this->runCommand('doctrine:schema:update', [
                '--em' => $manager,
                '--force' => true,
            ]);
        }
    }

    private function dropSchema(): void
    {
        // todo migration support

        foreach ($this->registry->getManagerNames() as $manager) { // todo customize object managers
            $this->runCommand('doctrine:schema:drop', [
                '--em' => $manager,
                '--force' => true,
            ]);
        }
    }

    /**
     * @param array<string,scalar> $parameters
     */
    private function runCommand(string $command, array $parameters = [], bool $canFail = false): void
    {
        $exit = $this->application()->run(
            new ArrayInput(\array_merge(['command' => $command], $parameters)),
            $output = new BufferedOutput()
        );

        if (0 !== $exit && !$canFail) {
            throw new \RuntimeException(\sprintf('Error running "%s": %s', $command, $output->fetch()));
        }
    }

    private function application(): Application
    {
        if (isset($this->application)) {
            return $this->application;
        }

        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);

        return $this->application;
    }
}
