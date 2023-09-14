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

use Zenstruck\Foundry\Tests\Fixture\Factories\GenericModelProxyFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class GenericProxyFactoryTestCase extends GenericFactoryTestCase
{
    /**
     * @test
     */
    public function can_update_and_delete_via_proxy(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function can_disable_persisting_by_factory_and_save_proxy(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function can_disable_and_enable_auto_refreshing(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function can_disable_and_enable_auto_refreshing_with_callback(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function can_manually_refresh_via_proxy(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function proxy_auto_refreshes(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function cannot_auto_refresh_proxy_if_changes(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function repository_methods_are_proxied(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function can_access_repository_from_proxy(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function global_proxy_functions(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function non_proxied_object_is_passed_to_after_instantiate_hook(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function proxied_non_auto_refreshing_object_is_passed_to_after_persist_hook(): void
    {
        $this->markTestIncomplete();
    }

    abstract protected function factory(): GenericModelProxyFactory; // @phpstan-ignore-line
}
