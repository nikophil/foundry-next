<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\InMemory;

/**
 * @template T of object
 */
interface InMemoryRepository
{
    /**
     * @param T $element
     */
    public function _save(object $element): void;
}
