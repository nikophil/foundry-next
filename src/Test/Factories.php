<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test;

use Zenstruck\Foundry\Configuration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait Factories
{
    /**
     * @internal
     * @before
     */
    public static function _bootFoundry(): void
    {
        Configuration::boot();
    }

    /**
     * @internal
     * @after
     */
    public static function _shutdownFoundry(): void
    {
        Configuration::shutdown();
    }
}
