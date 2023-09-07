<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zenstruck\Foundry\Factory\Persistence\ORM\ORMPersistenceManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckFoundryBundle extends AbstractBundle
{
    public function boot(): void
    {
        if ($this->container && !Configuration::isBooted()) {
            Configuration::boot($this->container->get('.zenstruck_foundry.configuration')); // @phpstan-ignore-line
        }
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->arrayNode('orm')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_persist')
                            ->info('Automatically persist entities when created.')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('auto_refresh')
                            ->info('Automatically refresh entities on entity method calls.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('connections')
                                    ->info('DBAL connections to reset with ResetDatabase trait')
                                    ->defaultValue(['default'])
                                    ->scalarPrototype()->end()
                                ->end()
                                ->arrayNode('entity_managers')
                                    ->info('Entity Managers to reset with ResetDatabase trait')
                                    ->defaultValue(['default'])
                                    ->scalarPrototype()->end()
                                ->end()
                                ->enumNode('mode')
                                    ->info('Reset mode to use with ResetDatabase trait')
                                    ->defaultValue(ORMPersistenceManager::RESET_MODE_SCHEMA)
                                    ->values([ORMPersistenceManager::RESET_MODE_SCHEMA, ORMPersistenceManager::RESET_MODE_MIGRATE])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mongo')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_persist')
                            ->info('Automatically persist documents when created.')
                            ->defaultTrue()
                        ->end()
                        ->booleanNode('auto_refresh')
                            ->info('Automatically refresh documents on entity method calls.')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('reset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('document_managers')
                                    ->info('Document Managers to reset with ResetDatabase trait')
                                    ->defaultValue(['default'])
                                    ->scalarPrototype()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $container->registerForAutoconfiguration(Factory::class)
            ->addTag('foundry.factory')
        ;

        $configurator->import('../config/services.php');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle']) || isset($bundles['DoctrineMongoDBBundle'])) {
            $configurator->import('../config/persistence.php');
        }

        if (isset($bundles['DoctrineBundle'])) {
            $configurator->import('../config/orm.php');

            $container->getDefinition('.zenstruck_foundry.persistence_manager.orm')
                ->replaceArgument(1, $config['orm'])
            ;
        }

        if (isset($bundles['DoctrineMongoDBBundle'])) {
            $configurator->import('../config/mongo.php');

            $container->getDefinition('.zenstruck_foundry.persistence_manager.mongo')
                ->replaceArgument(1, $config['mongo'])
            ;
        }
    }
}
