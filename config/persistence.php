<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\Persistence\PersistenceManagerRegistry;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.persistence_registry', PersistenceManagerRegistry::class)
            ->args([
                tagged_iterator('.foundry.persistence_manager'),
            ])
    ;
};
