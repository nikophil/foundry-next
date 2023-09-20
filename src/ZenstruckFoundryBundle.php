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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\ORM\ORMPersistenceStrategy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckFoundryBundle extends AbstractBundle implements CompilerPassInterface
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
                ->arrayNode('faker')
                    ->addDefaultsIfNotSet()
                    ->info('Configure the faker used by your factories.')
                    ->children()
                        ->scalarNode('locale')
                            ->info('The default locale to use for faker.')
                            ->example('fr_FR')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('seed')
                            ->info('Random number generator seed to produce the same fake values every run.')
                            ->example(1234)
                            ->defaultNull()
                        ->end()
                        ->scalarNode('service')
                            ->info('Service id for custom faker instance.')
                            ->example('my_faker')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('instantiator')
                    ->addDefaultsIfNotSet()
                    ->info('Configure the default instantiator used by your object factories.')
                    ->children()
                        ->booleanNode('use_constructor')
                            ->info('Use the constructor to instantiate objects.')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('service')
                            ->info('Service id of your custom instantiator.')
                            ->example('my_instantiator')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('mapper')
                    ->addDefaultsIfNotSet()
                    ->info('Configure the default property mapper used by your object factories.')
                    ->children()
                        ->booleanNode('allow_extra_attributes')
                            ->info('Whether or not to skip attributes that do not correspond to properties.')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('always_force_properties')
                            ->info('Whether or not to skip setters and force set object properties (public/private/protected) directly.')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('global_stories')
                    ->info('Global stories to be loaded before each test.')
                    ->validate()
                        ->ifTrue(function(array $stories) {
                            foreach ($stories as $story) {
                                if (!\is_a($story, Story::class, true)) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid(\sprintf('Global stories must extend "%s".', Story::class))
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('orm')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_persist')
                            ->info('Automatically persist entities when created.')
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
                                    ->defaultValue(ORMPersistenceStrategy::RESET_MODE_SCHEMA)
                                    ->values([ORMPersistenceStrategy::RESET_MODE_SCHEMA, ORMPersistenceStrategy::RESET_MODE_MIGRATE])
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

        $container->registerForAutoconfiguration(Story::class)
            ->addTag('foundry.story')
        ;

        $configurator->import('../config/services.php');

        $this->configureInstantiator($config['instantiator'], $container);
        $this->configureMapper($config['mapper'], $container);
        $this->configureFaker($config['faker'], $container);

        $container->getDefinition('.zenstruck_foundry.story_registry')
            ->replaceArgument(1, $config['global_stories'])
        ;

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle']) || isset($bundles['DoctrineMongoDBBundle'])) {
            $configurator->import('../config/persistence.php');
        }

        if (isset($bundles['DoctrineBundle'])) {
            $configurator->import('../config/orm.php');

            $container->getDefinition('.zenstruck_foundry.persistence_strategy.orm')
                ->replaceArgument(1, $config['orm'])
            ;
        }

        if (isset($bundles['DoctrineMongoDBBundle'])) {
            $configurator->import('../config/mongo.php');

            $container->getDefinition('.zenstruck_foundry.persistence_strategy.mongo')
                ->replaceArgument(1, $config['mongo'])
            ;
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass($this);
    }

    public function process(ContainerBuilder $container): void
    {
        // faker providers
        foreach ($container->findTaggedServiceIds('foundry.faker_provider') as $id => $tags) {
            $container
                ->getDefinition('.zenstruck_foundry.faker')
                ->addMethodCall('addProvider', [new Reference($id)])
            ;
        }
    }

    /**
     * @param mixed[] $config
     */
    private function configureInstantiator(array $config, ContainerBuilder $container): void
    {
        if ($config['service']) {
            $container->setAlias('.zenstruck_foundry.instantiator', $config['service']);

            return;
        }

        if (!$config['use_constructor']) {
            $container->getDefinition('.zenstruck_foundry.instantiator')
                ->setFactory([Instantiator::class, 'withoutConstructor'])
            ;
        }
    }

    /**
     * @param mixed[] $config
     */
    private function configureMapper(array $config, ContainerBuilder $container): void
    {
        if ($config['allow_extra_attributes']) {
            $container->getDefinition('.zenstruck_foundry.mapper')
                ->addMethodCall('allowExtra', returnsClone: true)
            ;
        }

        if ($config['always_force_properties']) {
            $container->getDefinition('.zenstruck_foundry.mapper')
                ->addMethodCall('alwaysForce', returnsClone: true)
            ;
        }
    }

    /**
     * @param mixed[] $config
     */
    private function configureFaker(array $config, ContainerBuilder $container): void
    {
        if ($config['service']) {
            $container->setAlias('.zenstruck_foundry.faker', $config['service']);

            return;
        }

        $definition = $container->getDefinition('.zenstruck_foundry.faker');

        if ($config['locale']) {
            $definition->addArgument($config['locale']);
        }

        if ($config['seed']) {
            $definition->addMethodCall('seed', [$config['seed']]);
        }
    }
}
