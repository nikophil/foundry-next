<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\ORM\ORMPersistenceManager;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.persistence_manager.orm', ORMPersistenceManager::class)
            ->args([
                service('doctrine'),
                abstract_arg('config'),
            ])
            ->tag('.foundry.persistence_manager')
    ;
};
