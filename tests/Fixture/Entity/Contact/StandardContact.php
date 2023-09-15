<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Entity\Contact;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Foundry\Tests\Fixture\Entity\Address;
use Zenstruck\Foundry\Tests\Fixture\Entity\Address\StandardAddress;
use Zenstruck\Foundry\Tests\Fixture\Entity\Category;
use Zenstruck\Foundry\Tests\Fixture\Entity\Category\StandardCategory;
use Zenstruck\Foundry\Tests\Fixture\Entity\Contact;
use Zenstruck\Foundry\Tests\Fixture\Entity\Tag\StandardTag;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity]
class StandardContact extends Contact
{
    #[ORM\ManyToOne(targetEntity: StandardCategory::class, inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: StandardTag::class, inversedBy: 'contacts')]
    protected Collection $tags;

    #[ORM\OneToOne(inversedBy: 'contact', targetEntity: StandardAddress::class)]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Address $address = null;
}
