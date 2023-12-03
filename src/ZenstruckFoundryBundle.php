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
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zenstruck\Foundry\InMemory\AsInMemoryRepository;
use Zenstruck\Foundry\InMemory\DependencyInjection\InMemoryCompilerPass;
use Zenstruck\Foundry\InMemory\InMemoryRepository;
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
                        ->booleanNode('allow_extra_attributes')
                            ->info('Whether or not to skip attributes that do not correspond to properties.')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('always_force_properties')
                            ->info('Whether or not to skip setters and force set object properties (public/private/protected) directly.')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('service')
                            ->info('Service id of your custom instantiator.')
                            ->example('my_instantiator')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('global_state')
                    ->info('Stories or invokable services to be loaded before each test.')
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
        $this->configureFaker($config['faker'], $container);
        $this->configureGlobalState($config['global_state'], $container);

        $bundles = $container->getParameter('kernel.bundles');

        $configurator->import(\sprintf('../config/%s.php',
            isset($bundles['MakerBundle']) ? 'makers' : 'command_stubs'
        ));

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

        // todo: should we find a way to decouple Foundry from its "plugins"?
        // tag with "foundry.in_memory.repository" all classes using attribute "AsInMemoryRepository"
        $container->registerAttributeForAutoconfiguration(
            AsInMemoryRepository::class,
            static function (ChildDefinition $definition, AsInMemoryRepository $attribute, \ReflectionClass $reflector) {
                if (!is_a($reflector->name, InMemoryRepository::class, true)) {
                    throw new \LogicException(sprintf("Service \"%s\" with attribute \"AsInMemoryRepository\" must implement \"%s\".", $reflector->name, InMemoryRepository::class));
                }

                $definition->addTag('foundry.in_memory.repository', ['class' => $attribute->class]);
            }
        );
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass($this);

        // todo: should we find a way to decouple Foundry from its "plugins"?
        $container->addCompilerPass(new InMemoryCompilerPass());
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
     * @param string[] $values
     */
    private function configureGlobalState(array $values, ContainerBuilder $container): void
    {
        $values = \array_map(
            static fn(string $v) => \is_a($v, Story::class, true) ? $v : new Reference($v),
            $values
        );

        $container->getDefinition('.zenstruck_foundry.story_registry')
            ->replaceArgument(1, $values)
        ;
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

        if ($config['allow_extra_attributes']) {
            $container->getDefinition('.zenstruck_foundry.instantiator')
                ->addMethodCall('allowExtra', returnsClone: true)
            ;
        }

        if ($config['always_force_properties']) {
            $container->getDefinition('.zenstruck_foundry.instantiator')
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
