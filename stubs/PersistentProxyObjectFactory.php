<?php

use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

use Zenstruck\Foundry\Persistence\Proxy;

use function PHPStan\Testing\assertType;

class User
{
    public string $name;
}

/**
 * The following method stubs are required for auto-completion in PhpStorm.
 *
 * @extends PersistentProxyObjectFactory<User>
 *
 * @method User|Proxy<User> create(array|callable $attributes = [])
 * @method static User|Proxy<User> createOne(array|callable $attributes = [])
 * @method static User[]|Proxy<User>[] createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy<User>[] randomRange(int $min, int $max, array $criteria = [])
 * @method static User[]|Proxy<User>[] randomSet(int $count, array $criteria = [])
 * @method static User|Proxy<User> first(string $sortBy = 'id')
 * @method static User|Proxy<User> last(string $sortBy = 'id')
 * @method static User|Proxy<User> find(mixed $criteriaOrId)
 * @method static User|Proxy<User> findOrCreate(array $criteria)
 * @method static User|Proxy<User> random(array $criteria = [])
 * @method static User|Proxy<User> randomOrCreate(array $criteria = [])
 * @method static User[]|Proxy<User>[] all()
 *
 * @phpstan-method (User&Proxy<User>) create(array|callable $attributes = [])
 * @phpstan-method static (User&Proxy<User>) createOne(array|callable $attributes = [])
 * @phpstan-method static (list<User&Proxy<User>>) createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static (User&Proxy<User>) first(string $sortBy = 'id')
 * @phpstan-method static (User&Proxy<User>) last(string $sortBy = 'id')
 * @phpstan-method static (User&Proxy<User>) find(mixed $criteriaOrId)
 * @phpstan-method static (User&Proxy<User>) findOrCreate(array $criteria)
 * @phpstan-method static (User&Proxy<User>) random(array $criteria = [])
 * @phpstan-method static (User&Proxy<User>) randomOrCreate(array $criteria = [])
 * @phpstan-method static (list<User&Proxy<User>>) all()
 * @phpstan-method static (list<User&Proxy<User>>) randomRange(int $min, int $max, array $criteria = [])
 * @phpstan-method static (list<User&Proxy<User>>) randomSet(int $count, array $criteria = [])
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [];
    }
}

// test autocomplete with phpstorm
assertType('string', UserFactory::new()->create()->name);
assertType('string', UserFactory::new()->create()->_real()->name);
assertType('string', UserFactory::createOne()->name);
assertType('string', UserFactory::createOne()->_real()->name);
assertType('string', UserFactory::new()->many(2)->create()[0]->name);
assertType('string', UserFactory::new()->many(2)->create()[0]->_real()->name); // cannot get auto-complete for _real() here
assertType('string', UserFactory::createMany(1)[0]->name);
assertType('string', UserFactory::createMany(1)[0]->_real()->name); // cannot get auto-complete for name here
assertType('string', UserFactory::first()->name);
assertType('string', UserFactory::first()->_real()->name);
assertType('string', UserFactory::last()->name);
assertType('string', UserFactory::last()->_real()->name);
assertType('string', UserFactory::find(1)->name);
assertType('string', UserFactory::find(1)->_real()->name);
assertType('string', UserFactory::all()[0]->name);
assertType('string', UserFactory::all()[0]->_real()->name); // cannot get auto-complete for name here
assertType('string', UserFactory::random()->name);
assertType('string', UserFactory::random()->_real()->name);
assertType('string', UserFactory::randomRange(1, 2)[0]->name);
assertType('string', UserFactory::randomRange(1, 2)[0]->_real()->name); // cannot get auto-complete for name here
assertType('string', UserFactory::randomSet(2)[0]->name);
assertType('string', UserFactory::randomSet(2)[0]->_real()->name); // cannot get auto-complete for name here
assertType('string', UserFactory::findBy(['name' => 'foo'])[0]->name);
assertType('string', UserFactory::findBy(['name' => 'foo'])[0]->_real()->name); // cannot get auto-complete for name here
assertType('string', UserFactory::findOrCreate([])->name);
assertType('string', UserFactory::findOrCreate([])->_real()->name);
assertType('string', UserFactory::randomOrCreate([])->name);
assertType('string', UserFactory::randomOrCreate([])->_real()->name);
assertType('string|null', UserFactory::repository()->find(1)?->name);
assertType('string|null', UserFactory::repository()->find(1)?->_real()->name); // cannot get auto-complete for _real() here
assertType('string', UserFactory::repository()->findAll()[0]->name);
assertType('string', UserFactory::repository()->findAll()[0]->_real()->name); // cannot get auto-complete for _real() here
assertType('string|null', UserFactory::createOne()->_repo()->find(1)?->name); // cannot get auto-complete for name here
assertType('string|null', UserFactory::createOne()->_repo()->find(1)?->_real()->name); // cannot get auto-complete for _real() here


