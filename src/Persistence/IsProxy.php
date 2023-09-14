<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Persistence;

use Symfony\Component\VarExporter\Internal\LazyObjectState;
use Zenstruck\Foundry\Configuration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 *
 * @property LazyObjectState $lazyObjectState
 * @method   object          initializeLazyObject()
 */
trait IsProxy
{
    private ?bool $_autoRefresh = null;

    public function _enableAutoRefresh(): static
    {
        $this->_autoRefresh = true;

        return $this;
    }

    public function _disableAutoRefresh(): static
    {
        $this->_autoRefresh = false;

        return $this;
    }

    public function _withoutAutoRefresh(callable $callback): static
    {
        $original = $this->_autoRefresh;
        $this->_autoRefresh = false;

        $callback($this);

        $this->_autoRefresh = $original;

        return $this;
    }

    public function _save(): static
    {
        Configuration::instance()->persistence()->save($this->_real());

        return $this;
    }

    public function _refresh(): static
    {
        $this->initializeLazyObject();
        $object = $this->lazyObjectState->realInstance;

        Configuration::instance()->persistence()->refresh($object);

        $this->lazyObjectState->realInstance = $object;

        return $this;
    }

    public function _delete(): static
    {
        Configuration::instance()->persistence()->delete($this->_real());

        return $this;
    }

    public function _real(): object
    {
        return $this->initializeLazyObject();
    }

    public function _repo(): RepositoryDecorator
    {
        return new RepositoryDecorator(parent::class, true);
    }

    public function _resetAutoRefresh(): void
    {
        $this->_autoRefresh = null;
    }

    private function _autoRefresh(): void
    {
        if (false === $this->_autoRefresh) {
            // unit test
            return;
        }

        $persistenceManager = Configuration::instance()->persistence();

        if (!($this->_autoRefresh ?? $persistenceManager->autoRefreshProxies(parent::class))) {
            return;
        }

        $this->_refresh();
    }
}
