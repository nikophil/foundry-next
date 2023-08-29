<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Faker;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory\FactoryRegistry;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.faker', Faker\Generator::class)
            ->factory([Faker\Factory::class, 'create'])

        ->set('.zenstruck_foundry.factory_registry', FactoryRegistry::class)
            ->args([tagged_iterator('foundry.factory')])

        ->set('.zenstruck_foundry.configuration', Configuration::class)
            ->args([
                service('.zenstruck_foundry.factory_registry'),
                service('.zenstruck_foundry.faker'),
            ])
            ->public()
    ;
};
