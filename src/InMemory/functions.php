<?php

namespace Zenstruck\Foundry\InMemory;

use Zenstruck\Foundry\Configuration;


/**
 * Enable "in memory" repositories globally.
 */
function enable_in_memory(): void
{
    Configuration::instance()->inMemory()->enableInMemory();
}
