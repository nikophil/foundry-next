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

use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Configuration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
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
        $om = self::_objectManager();
        $om->persist($this->initializeLazyObject());
        $om->flush();

        return $this;
    }

    public function _refresh(): static
    {
        $om = self::_objectManager();

        if ($om->contains($this->initializeLazyObject())) {
            $om->refresh($this->lazyObjectState->realInstance);

            return $this;
        }

        $id = $om->getClassMetadata(parent::class)->getIdentifierValues($this->lazyObjectState->realInstance);

        if (!$id || !$object = $om->find(parent::class, $id)) {
            throw new \RuntimeException('object no longer exists...');
        }

        $this->lazyObjectState->realInstance = $object;

        return $this;
    }

    public function _delete(): static
    {
        $om = self::_objectManager();
        $om->remove($this->initializeLazyObject());
        $om->flush();

        return $this;
    }

    public function _object(): object
    {
        return $this->initializeLazyObject();
    }

    public function _repo(): RepositoryDecorator
    {
        return self::_persistenceManager()->repositoryFor(parent::class);
    }

    private static function _objectManager(): ObjectManager
    {
        return self::_persistenceManager()->objectManagerFor(parent::class);
    }

    private static function _persistenceManager(): PersistenceManager
    {
        return Configuration::instance()->persistence()->managerFor(parent::class);
    }

    private function _autoRefresh(): void
    {
        if (null === $this->_autoRefresh && !Configuration::instance()->isPersistenceEnabled()) {
            // unit test
            return;
        }

        if (!($this->_autoRefresh ?? self::_persistenceManager()->autoRefresh())) {
            return;
        }

        if (self::_persistenceManager()->hasChanges($this->initializeLazyObject())) {
            throw new \RuntimeException(\sprintf('Cannot auto refresh "%s" as there are unsaved changes. Be sure to call ->_save() or disable auto refreshing (see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#auto-refresh for details).', parent::class));
        }

        $this->_refresh();
    }
}
