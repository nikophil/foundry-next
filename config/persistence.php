<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\Factory\Persistence\ChainPersistenceManager;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.persistence_manager', ChainPersistenceManager::class)
            ->args([
                tagged_iterator('.foundry.persistence_manager'),
            ])
    ;
};
