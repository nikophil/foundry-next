<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Zenstruck\Foundry\Tests\Fixture\Model\Relation;
use Zenstruck\Foundry\Tests\Fixture\Model\StandardModel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[MongoDB\Document]
class StandardDocument extends StandardModel
{
    #[MongoDB\EmbedOne(targetDocument: EmbeddedDocument::class)]
    protected ?Relation $relation = null;
}
