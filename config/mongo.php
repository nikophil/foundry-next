<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\Factory\Persistence\Mongo\MongoPersistenceManager;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.persistence_manager.mongo', MongoPersistenceManager::class)
            ->args([
                service('doctrine_mongodb'),
                abstract_arg('config'),
            ])
            ->tag('.foundry.persistence_manager')
    ;
};
