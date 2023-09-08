<?php

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

use function PHPStan\Testing\assertType;

class User
{
    public function name(): string
    {
        return 'name';
    }
}

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends PersistentObjectFactory<User>
 *
 * @method static User|Proxy createOne(array|callable $attributes = [])
 * @method static User|Proxy first(string $sortBy = null)
 * @method static User|Proxy last(string $sortBy = null)
 * @method static list<User|Proxy> createMany(int $number, array|callable $attributes = [])
 * @method static list<User|Proxy> all()
 *
 * @phpstan-method static (User&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (User&Proxy) first(string $sortBy = null)
 * @phpstan-method static (User&Proxy) last(string $sortBy = null)
 * @phpstan-method static (list<User&Proxy>) createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static (list<User&Proxy>) all()
 */
final class UserFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [];
    }

    public static function class(): string
    {
        return User::class;
    }
}

/** @var UserFactory $factory */

assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory->create());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory->create()->_refresh());
assertType('string', $factory->create()->name());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::createOne());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::createOne()->_refresh());
assertType('string', $factory::createOne()->name());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::first());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::first()->_refresh());
assertType('string', $factory::first()->name());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::last());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::last()->_refresh());
assertType('string', $factory::last()->name());
assertType('Zenstruck\Foundry\Persistence\RepositoryDecorator<User>', $factory::repository());
assertType('(User&Zenstruck\Foundry\Persistence\Proxy)|null', $factory::repository()->find(1));
assertType('array<int, User&Zenstruck\Foundry\Persistence\Proxy>', $factory::repository()->findAll());
assertType('array<int, User&Zenstruck\Foundry\Persistence\Proxy>', $factory::repository()->findBy([]));
assertType('(User&Zenstruck\Foundry\Persistence\Proxy)|null', $factory::repository()->findOneBy([]));
assertType('array<int, User&Zenstruck\Foundry\Persistence\Proxy>', $factory::all());
assertType('string', $factory::all()[0]->name());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::all()[0]->_refresh());
assertType('array<int, User&Zenstruck\Foundry\Persistence\Proxy>', $factory::createMany(2));
assertType('array<int, User&Zenstruck\Foundry\Persistence\Proxy>', $factory->many(1)->create());
assertType('string', $factory::createMany(2)[0]->name());
assertType('User&Zenstruck\Foundry\Persistence\Proxy', $factory::createMany(2)[0]->_refresh());
