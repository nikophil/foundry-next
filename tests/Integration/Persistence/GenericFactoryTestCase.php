<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Persistence;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\GenericModelFactory;
use Zenstruck\Foundry\Tests\Fixture\Model\GenericModel;

use function Zenstruck\Foundry\persistent_object;
use function Zenstruck\Foundry\repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class GenericFactoryTestCase extends KernelTestCase
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

        $object->_withoutAutoRefresh(function(GenericModel&Proxy $object) {
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
        repository($this->modelClass())->assert()->empty();

        $object = persistent_object($this->modelClass(), ['prop1' => 'value']);

        $this->assertNotNull($object->id);
        $this->assertSame('value', $object->getProp1());
        $this->assertSame('value', $object->_refresh()->getProp1());

        repository($this->modelClass())->assert()->count(1);
    }

    /**
     * @test
     */
    public function create_many(): void
    {
        $models = $this->factory()->createMany(3, fn(int $i) => ['prop1' => "value{$i}"]);

        $this->factory()::repository()->assert()->count(3);

        $this->assertSame('value1', $models[0]->getProp1());
        $this->assertSame('value2', $models[1]->getProp1());
        $this->assertSame('value3', $models[2]->getProp1());
    }

    /**
     * @test
     */
    public function sequences(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function as_data_provider(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function find(): void
    {
        $object = $this->factory()->create(['prop1' => 'foo']);

        $this->assertSame($object->id, $this->factory()::find($object->id)->id);
        $this->assertSame($object->id, $this->factory()::find(['prop1' => 'foo'])->id);
    }

    /**
     * @test
     */
    public function find_by(): void
    {
        $this->factory()->create(['prop1' => 'a']);
        $this->factory()->create(['prop1' => 'b']);
        $this->factory()->create(['prop1' => 'b']);

        $this->assertCount(1, $this->factory()::findBy(['prop1' => 'a']));
        $this->assertCount(2, $this->factory()::findBy(['prop1' => 'b']));
    }

    /**
     * @test
     */
    public function find_or_create(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function random(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function random_or_create(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function random_set(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function random_range(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function factory_count(): void
    {
        $this->factory()::createOne(['prop1' => 'a']);
        $this->factory()::createOne(['prop1' => 'b']);
        $this->factory()::createOne(['prop1' => 'b']);

        $this->assertSame(3, $this->factory()::count());
        $this->assertSame(2, $this->factory()::count(['prop1' => 'b']));
    }

    /**
     * @test
     */
    public function truncate(): void
    {
        $this->factory()::createMany(3);
        $this->factory()::repository()->assert()->count(3);

        $this->factory()::truncate();

        $this->factory()::repository()->assert()->empty();
    }

    /**
     * @test
     */
    public function factory_all(): void
    {
        $this->factory()::createMany(3);

        $this->assertCount(3, $this->factory()::all());
    }

    /**
     * @test
     */
    public function repository_assertions(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return class-string<GenericModel>
     */
    protected function modelClass(): string
    {
        return $this->factory()::class();
    }

    abstract protected function factory(): GenericModelFactory;
}
