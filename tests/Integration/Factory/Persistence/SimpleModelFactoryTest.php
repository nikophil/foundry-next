<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Factory\Persistence;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Factory\Persistence\Proxy;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\SimpleModelFactory;
use Zenstruck\Foundry\Tests\Fixture\Model\SimpleModel;

use function Zenstruck\Foundry\persistent_object;
use function Zenstruck\Foundry\repo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template M of SimpleModel
 * @template F of SimpleModelFactory<M>
 */
abstract class SimpleModelFactoryTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function can_create_and_update(): void
    {
        $this->factory()->repository()->assert()->empty();

        $object = $this->factory()->create();

        $this->assertNotNull($object->id);
        $this->assertSame('default1', $object->getProp1());
        $this->assertSame('default1', $object->_refresh()->getProp1());

        $this->factory()->repository()->assert()
            ->count(1)
            ->exists(['prop1' => 'default1'])
            ->notExists(['prop1' => 'invalid'])
        ;

        $this->assertSame($object->id, $this->factory()->first()->id);
        $this->assertSame($object->id, $this->factory()->last()->id);

        $object->setProp1('new value');
        $object->_save();

        $this->assertSame('new value', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'new value']);
    }

    /**
     * @test
     */
    public function can_disable_auto_persist(): void
    {
        $this->factory()->repository()->assert()->empty();

        $object = $this->factory()->withoutPersisting()->create();

        $this->assertNull($object->id);
        $this->assertSame('default1', $object->getProp1());

        $this->factory()->repository()->assert()->empty();

        $object->_save();

        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);
    }

    /**
     * @test
     */
    public function auto_refreshes(): void
    {
        $object = $this->factory()->create();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        self::ensureKernelShutdown();

        // modify and save title "externally"
        $ext = $this->factory()->first();
        $ext->setProp1('external');
        $ext->_save();

        self::ensureKernelShutdown();

        // "calling method" triggers auto-refresh
        $this->assertSame('external', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'external']);
    }

    /**
     * @test
     */
    public function cannot_auto_refresh_if_changes_detected(): void
    {
        $object = $this->factory()->create();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        $object->setProp1('new');

        try {
            $object->setProp1('new 1');
        } catch (\RuntimeException) {
            $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);
            $object->_save();
            $this->assertSame('new', $object->getProp1());
            $this->factory()->repository()->assert()->exists(['prop1' => 'new']);

            return;
        }

        $this->fail('Exception not thrown');
    }

    /**
     * @test
     */
    public function can_disable_auto_refresh(): void
    {
        $object = $this->factory()->create();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        $object->_disableAutoRefresh();
        $object->setProp1('new');
        $object->setProp1('new 2');
        $object->_enableAutoRefresh();
        $object->_save();

        $this->assertSame('new 2', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'new 2']);
    }

    /**
     * @test
     */
    public function can_manually_refresh(): void
    {
        $object = $this->factory()->create()->_disableAutoRefresh();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        self::ensureKernelShutdown();

        // modify and save title "externally"
        $ext = $this->factory()->first();
        $ext->setProp1('external');
        $ext->_save();

        self::ensureKernelShutdown();

        // "calling method" triggers auto-refresh
        $this->assertSame('external', $object->_refresh()->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'external']);
    }

    /**
     * @test
     */
    public function can_disable_auto_refresh_with_callback(): void
    {
        $object = $this->factory()->create();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        $object->_withoutAutoRefresh(function(SimpleModel&Proxy $object) {
            $object->setProp1('new');
            $object->setProp1('new 2');
            $object->_save();
        });

        $this->assertSame('new 2', $object->getProp1());
        $this->factory()->repository()->assert()->exists(['prop1' => 'new 2']);
    }

    /**
     * @test
     */
    public function can_delete(): void
    {
        $object = $this->factory()->create();

        $this->factory()->repository()->assert()->exists(['prop1' => 'default1']);

        $object->_delete();

        $this->factory()->repository()->assert()->empty();
    }

    /**
     * @test
     */
    public function repository_and_create_function(): void
    {
        repo($this->modelClass())->assert()->empty();

        $object = persistent_object($this->modelClass(), ['prop1' => 'value']);

        $this->assertNotNull($object->id);
        $this->assertSame('value', $object->getProp1());
        $this->assertSame('value', $object->_refresh()->getProp1());

        repo($this->modelClass())->assert()->count(1);
    }

    /**
     * @return class-string<M>
     */
    abstract protected function modelClass(): string;

    /**
     * @return F
     */
    abstract protected function factory(): SimpleModelFactory;
}
