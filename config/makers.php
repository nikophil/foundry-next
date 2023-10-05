<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Foundry\Maker\MakeStory;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.zenstruck_foundry.make_story', MakeStory::class)
            ->tag('maker.command')
    ;
};
