<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Factories;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\Foundry\ArrayFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ServiceArrayFactory extends ArrayFactory
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    protected function defaults(): array|callable
    {
        return [
            'router' => (bool) $this->router,
            'fake' => self::faker()->randomElement(['value']),
        ];
    }
}
