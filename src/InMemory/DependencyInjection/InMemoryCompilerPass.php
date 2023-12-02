<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\InMemory\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zenstruck\Foundry\InMemory\AsInMemoryRepository;
use Zenstruck\Foundry\InMemory\InMemoryFactoryRegistry;
use Zenstruck\Foundry\InMemory\InMemoryManager;
use Zenstruck\Foundry\InMemory\InMemoryRepository;

/**
 * @internal
 */
final class InMemoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // create a service locator with all "in memory" repositories, indexed by target class
        $inMemoryRepositoriesServices = $container->findTaggedServiceIds('foundry.in_memory.repository');
        $inMemoryRepositoriesLocator = ServiceLocatorTagPass::register(
            $container,
            array_combine(
                array_map(
                    static function (array $tags) {
                        if (\count($tags) !== 1) {
                            throw new \LogicException('Cannot have multiple tags "foundry.in_memory.repository" on a service!');
                        }

                        return $tags[0]['class'] ?? throw new \LogicException('Invalid tag definition of "foundry.in_memory.repository".');
                    },
                    array_values($inMemoryRepositoriesServices)
                ),
                array_map(
                    static fn(string $inMemoryRepositoryId) => new Reference($inMemoryRepositoryId),
                    array_keys($inMemoryRepositoriesServices)
                ),
            )
        );

        // todo: should we check we only have a 1 repository per class?

        $container->register('.zenstruck_foundry.in_memory.manager')
            ->setClass(InMemoryManager::class)
            ->setArgument('$inMemoryRepositories', $inMemoryRepositoriesLocator)
        ;

        $container->register('.zenstruck_foundry.in_memory.factory_registry')
            ->setClass(InMemoryFactoryRegistry::class)
            ->setDecoratedService('.zenstruck_foundry.factory_registry')
            ->setArgument('$decorated', $container->getDefinition('.zenstruck_foundry.factory_registry'))
            ->setArgument('$inMemoryManager', new Reference('.zenstruck_foundry.in_memory.manager'))
        ;
    }
}
