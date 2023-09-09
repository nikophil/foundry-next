<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ORM\Mapping as ORM;

/**
 * Used for global story tests.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\MappedSuperclass]
#[MongoDB\MappedSuperclass]
abstract class GlobalModel
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[MongoDB\Id(type: 'int', strategy: 'INCREMENT')]
    public ?int $id = null;
}
