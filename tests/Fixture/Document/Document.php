<?php

namespace Zenstruck\Foundry\Tests\Fixture\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Zenstruck\Foundry\Tests\Fixture\Model\Base;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[MongoDB\Document]
class Document extends Base
{
    #[MongoDB\EmbedOne(nullable: true, targetDocument: Embeddable::class)]
    private ?Embeddable $embeddable;

    /** @var Collection<int,Embeddable>  */
    #[MongoDB\EmbedMany(targetDocument: Embeddable::class)]
    private Collection $embeddables;

    public function __construct(?Embeddable $embeddable = null)
    {
        $this->embeddable = $embeddable;
        $this->embeddables = new ArrayCollection();
    }

    public function getEmbeddable(): ?Embeddable
    {
        return $this->embeddable;
    }

    public function addEmbeddable(Embeddable $embeddable): void
    {
        $this->embeddables->add($embeddable);
    }

    public function removeEmbeddable(Embeddable $embeddable): void
    {
        $this->embeddables->removeElement($embeddable);
    }

    /**
     * @return Collection<int,Embeddable>
     */
    public function getEmbeddables(): Collection
    {
        return $this->embeddables;
    }
}
