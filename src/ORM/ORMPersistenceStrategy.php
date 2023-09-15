<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\ORM;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpKernel\KernelInterface;
use Zenstruck\Foundry\Persistence\PersistenceManager;
use Zenstruck\Foundry\Persistence\PersistenceStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 *
 * @method EntityManagerInterface objectManagerFor(string $class)
 */
final class ORMPersistenceStrategy extends PersistenceStrategy
{
    public const RESET_MODE_SCHEMA = 'schema';
    public const RESET_MODE_MIGRATE = 'migrate';

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

    public function truncate(string $class): void
    {
        $this->objectManagerFor($class)->createQuery("DELETE {$class} e")->execute();
    }

    public function resetDatabase(KernelInterface $kernel): void
    {
        $application = self::application($kernel);

        foreach ($this->connections() as $connection) {
            $databasePlatform = $this->registry->getConnection($connection)->getDatabasePlatform(); // @phpstan-ignore-line

            if ($databasePlatform instanceof PostgreSQLPlatform) {
                // let's drop all connections to the database to be able to drop it
                self::runCommand(
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

            self::runCommand($application, 'doctrine:database:drop', $dropParams);
            self::runCommand($application, 'doctrine:database:create', ['--connection' => $connection]);
        }

        $this->createSchema($application);
    }

    public function resetSchema(KernelInterface $kernel): void
    {
        if (PersistenceManager::isDAMADoctrineTestBundleEnabled()) {
            // not required as the DAMADoctrineTestBundle wraps each test in a transaction
            return;
        }

        $application = self::application($kernel);

        $this->dropSchema($application);
        $this->createSchema($application);
    }

    public function inverseRelationshipFieldFor(string $owner, string $inverse): ?string
    {
        $metadata = $this->objectManagerFor($owner)->getClassMetadata($owner);

        foreach ($metadata->getAssociationNames() as $association) {
            // ensure 1-n and associated class matches
            if ($metadata->isSingleValuedAssociation($association) && $metadata->getAssociationTargetClass($association) === $inverse) {
                return $association;
            }
        }

        return null;
    }

    private function createSchema(Application $application): void
    {
        if (self::RESET_MODE_MIGRATE === $this->config['reset']['mode']) {
            self::runCommand($application, 'doctrine:migrations:migrate', [
                '--no-interaction' => true,
            ]);

            return;
        }

        foreach ($this->managers() as $manager) {
            self::runCommand($application, 'doctrine:schema:update', [
                '--em' => $manager,
                '--force' => true,
            ]);
        }
    }

    private function dropSchema(Application $application): void
    {
        foreach ($this->managers() as $manager) {
            self::runCommand($application, 'doctrine:schema:drop', [
                '--em' => $manager,
                '--force' => true,
                '--full-database' => true,
            ]);
        }
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
