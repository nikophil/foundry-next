<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Unit\Factory;

use Zenstruck\Foundry\Factory\Object\Instantiator;
use Zenstruck\Foundry\Factory\Object\Mapper;
use Zenstruck\Foundry\Tests\Fixture\Entity\SimpleEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandaloneObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\SimpleObject;

use function Zenstruck\Foundry\factory;
use function Zenstruck\Foundry\object;
use function Zenstruck\Foundry\persistent_factory;
use function Zenstruck\Foundry\persistent_object;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait StandaloneObjectFactoryTests
{
    /**
     * @test
     */
    public function defaults(): void
    {
        $object = StandaloneObjectFactory::createOne();

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('default-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function named_constructor_defaults(): void
    {
        $object = StandaloneObjectFactory::new()->instantiateWith(Instantiator::namedConstructor('factory'))->create();

        $this->assertSame('value1-named-constructor', $object->getProp1());
        $this->assertSame('default-named-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function default_instantiator_and_mapper(): void
    {
        $object = StandaloneObjectFactory::createOne([
            'prop1' => 'override1',
            'prop2' => 'override2',
            'prop3' => 'override3',
        ]);

        $this->assertSame('override1-constructor', $object->getProp1());
        $this->assertSame('override2-constructor', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function without_constructor_instantiator(): void
    {
        $object = StandaloneObjectFactory::new()->instantiateWith(Instantiator::withoutConstructor())->create([
            'prop1' => 'override1',
            'prop2' => 'override2',
            'prop3' => 'override3',
        ]);

        $this->assertSame('override1-setter', $object->getProp1());
        $this->assertSame('override2-setter', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_closure_factory_constructor(): void
    {
        $object = StandaloneObjectFactory::new()
            ->instantiateWith(Instantiator::with(fn(string $prop1) => new SimpleObject($prop1)))
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
            ])
        ;

        $this->assertSame('override1-constructor', $object->getProp1());
        $this->assertSame('override2-setter', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_method_factory_constructor(): void
    {
        $object = StandaloneObjectFactory::new()
            ->instantiateWith(Instantiator::with(SimpleObject::factory(...)))
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
            ])
        ;

        $this->assertSame('override1-named-constructor', $object->getProp1());
        $this->assertSame('override2-named-constructor', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_named_constructor_instantiator(): void
    {
        $object = StandaloneObjectFactory::new()->instantiateWith(Instantiator::namedConstructor('factory'))->create([
            'prop1' => 'override1',
            'prop2' => 'override2',
            'prop3' => 'override3',
        ]);

        $this->assertSame('override1-named-constructor', $object->getProp1());
        $this->assertSame('override2-named-constructor', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_extra_and_force_mode_without_constructor(): void
    {
        $object = StandaloneObjectFactory::new()
            ->instantiateWith(Instantiator::withoutConstructor())
            ->configureMapping(Mapper::ALLOW_EXTRA_ATTRIBUTES | Mapper::ALWAYS_FORCE_PROPERTIES)
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
                'extra' => 'value',
            ])
        ;

        $this->assertSame('override1', $object->getProp1());
        $this->assertSame('override2', $object->getProp2());
        $this->assertSame('override3', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_configured_mapper(): void
    {
        $object = StandaloneObjectFactory::new()
            ->instantiateWith(Instantiator::withoutConstructor())
            ->configureMapping(fn(Mapper $m) => $m->allowExtra('extra')->alwaysForce('prop2'))
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
                'extra' => 'value',
            ])
        ;

        $this->assertSame('override1-setter', $object->getProp1());
        $this->assertSame('override2', $object->getProp2());
        $this->assertSame('override3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function with_mapper_disabled(): void
    {
        $object = StandaloneObjectFactory::new()
            ->disableMapping()
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
                'extra' => 'value',
            ])
        ;

        $this->assertSame('override1-constructor', $object->getProp1());
        $this->assertSame('override2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function with_custom_instantiator_callable(): void
    {
        $object = StandaloneObjectFactory::new()
            ->instantiateWith(fn() => new SimpleObject('custom'))
            ->create([
                'prop1' => 'override1',
                'prop2' => 'override2',
                'prop3' => 'override3',
                'extra' => 'value',
            ])
        ;

        $this->assertSame('custom-constructor', $object->getProp1());
        $this->assertSame('default-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function before_instantiate_hook(): void
    {
        $object = StandaloneObjectFactory::new()
            ->beforeInstantiate(function(array $parameters, string $class) {
                $this->assertSame(['prop1' => 'value1'], $parameters);
                $this->assertSame(SimpleObject::class, $class);

                return [
                    'prop1' => 'custom1',
                    'prop2' => 'custom2',
                    'prop3' => 'custom3',
                ];
            })
            ->create()
        ;

        $this->assertSame('custom1-constructor', $object->getProp1());
        $this->assertSame('custom2-constructor', $object->getProp2());
        $this->assertSame('custom3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function after_instantiate_hook(): void
    {
        $object = StandaloneObjectFactory::new()
            ->afterInstantiate(function(SimpleObject $object, array $parameters) {
                $this->assertSame([], $parameters);

                $object->setProp3('custom3');
            })
            ->create()
        ;

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('default-constructor', $object->getProp2());
        $this->assertSame('custom3-setter', $object->getProp3());
    }

    /**
     * @test
     */
    public function create_anonymous_factory(): void
    {
        $object = factory(SimpleObject::class, ['prop1' => 'value1'])->create(['prop2' => 'value2']);

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('value2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());

        $object = factory(SimpleObject::class, ['prop1' => 'value1'])->create(['prop2' => 'value2']);

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('value2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());

        $object = object(SimpleObject::class, ['prop1' => 'value1', 'prop2' => 'value2']);

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('value2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());

        $object = persistent_factory(SimpleEntity::class, ['prop1' => 'value1'])->create();

        $this->assertSame('value1', $object->getProp1());

        $object = persistent_object(SimpleEntity::class, ['prop1' => 'value1']);

        $this->assertSame('value1', $object->getProp1());
    }
}
