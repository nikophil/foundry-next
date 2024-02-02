<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\ORM;

use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\GenericProxyEntityFactory;
use Zenstruck\Foundry\Tests\Integration\Persistence\GenericProxyFactoryTestCase;
use Zenstruck\Foundry\Tests\Integration\RequiresORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GenericEntityProxyFactoryTest extends GenericProxyFactoryTestCase
{
    use RequiresORM;

    /**
     * @test
     */
    public function test_modifier_which_calls_other_internal_method(): void
    {
        $object = $this->factory()->create();
        $object->setProp1('foo');
        $object->_save();
    }

    protected function factory(): PersistentProxyObjectFactory
    {
        return GenericProxyEntityFactory::new();
    }
}
