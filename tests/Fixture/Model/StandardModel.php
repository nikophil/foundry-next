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

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\MappedSuperclass]
class StandardModel
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public ?int $id = null;

    #[ORM\Column]
    private string $prop1;

    public function __construct(string $prop1)
    {
        $this->prop1 = $prop1;
    }

    public function getProp1(): string
    {
        return $this->prop1;
    }

    public function setProp1(string $prop1): void
    {
        $this->prop1 = $prop1;
    }
}
