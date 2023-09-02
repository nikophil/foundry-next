<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture;

use DAMA\DoctrineTestBundle\DAMADoctrineTestBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Foundry\Tests\Fixture\Factories\ServiceArrayFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\ServiceObjectFactory;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();

        if (\getenv('DATABASE_URL')) {
            yield new DoctrineBundle();
            yield new DoctrineMigrationsBundle();
        }

        yield new ZenstruckFoundryBundle();

        if (\getenv('USE_DAMA_DOCTRINE_TEST_BUNDLE')) {
            yield new DAMADoctrineTestBundle();
        }
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'http_method_override' => false,
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
        ]);

        $c->loadFromExtension('zenstruck_foundry', [
            'orm' => [
                'reset' => [
                    'mode' => \getenv('DATABASE_RESET_MODE') ?: 'schema',
                ],
            ],
        ]);

        if (\getenv('DATABASE_URL')) {
            $c->loadFromExtension('doctrine', [
                'dbal' => ['url' => \getenv('DATABASE_URL')],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'auto_mapping' => true,
                    'mappings' => [
                        'Entity' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Fixture/Entity',
                            'prefix' => 'Zenstruck\Foundry\Tests\Fixture\Entity',
                            'alias' => 'Entity',
                        ],
                        'Model' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => '%kernel.project_dir%/tests/Fixture/Model',
                            'prefix' => 'Zenstruck\Foundry\Tests\Fixture\Model',
                            'alias' => 'Model',
                        ],
                    ],
                ],
            ]);

            $c->loadFromExtension('doctrine_migrations', [
                'migrations_paths' => [
                    'Zenstruck\Foundry\Tests\Fixture\Migrations' => '%kernel.project_dir%/tests/Fixture/Migrations',
                ],
            ]);
        }

        $c->register(ServiceArrayFactory::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(ServiceObjectFactory::class)->setAutowired(true)->setAutoconfigured(true);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
