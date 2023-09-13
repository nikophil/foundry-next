<?php

namespace Zenstruck\Foundry\Tests\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[MongoDB\EmbeddedDocument]
final class Embeddable
{
    #[MongoDB\Field(type: 'string')]
    private string $prop1;

    public function __construct(string $prop1)
    {
        $this->prop1 = $prop1;
    }

    public function getProp1(): string
    {
        return $this->prop1;
    }
}
