<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Foundry\Tests\Fixture\Model\Model2;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity]
class CascadeEntity4 extends Model2
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'relation', targetEntity: CascadeEntity1::class, cascade: ['persist'])]
    protected Collection $models;
}
