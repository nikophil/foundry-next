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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

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

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $container->registerForAutoconfiguration(Factory::class)
            ->addTag('foundry.factory')
        ;

        $configurator->import('../config/services.php');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            $configurator->import('../config/persistence.php');
            $configurator->import('../config/orm.php');
        }
    }
}
