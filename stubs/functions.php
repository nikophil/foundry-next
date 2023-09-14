<?php

use function PHPStan\Testing\assertType;
use function Zenstruck\Foundry\factory;
use function Zenstruck\Foundry\object;
use function Zenstruck\Foundry\Persistence\persist;
use function Zenstruck\Foundry\Persistence\persistent_factory;
use function Zenstruck\Foundry\Persistence\proxy_factory;
use function Zenstruck\Foundry\Persistence\proxy_persist;
use function Zenstruck\Foundry\Persistence\proxy_repository;
use function Zenstruck\Foundry\Persistence\repository;

class User
{
    public string $name;
}

assertType('string', factory(User::class)->create()->name);
assertType('string', object(User::class)->name);

assertType('string', persistent_factory(User::class)->create()->name);
assertType('string', persist(User::class)->name);

assertType('string', proxy_factory(User::class)->create()->name);
assertType('string', proxy_factory(User::class)->create()->_refresh()->_real()->name);
assertType('string', proxy_persist(User::class)->name);
assertType('string', proxy_persist(User::class)->_refresh()->_real()->name);

assertType('User|null', repository(User::class)->find(1));
assertType('(User&Zenstruck\Foundry\Persistence\Proxy<User>)|null', proxy_repository(User::class)->find(1));
