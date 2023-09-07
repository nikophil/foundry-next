<?php

namespace Zenstruck\Foundry\Tests\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Zenstruck\Foundry\Tests\Fixture\Model\StandardModel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[MongoDB\Document]
class StandardDocument extends StandardModel
{
}
