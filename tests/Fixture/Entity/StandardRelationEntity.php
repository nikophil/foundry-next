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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Foundry\Tests\Fixture\Model\Relation;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity]
class StandardRelationEntity extends Relation
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    public ?int $id = null;

    /** @var Collection<int,StandardEntity> */
    #[ORM\OneToMany(mappedBy: 'relation', targetEntity: StandardEntity::class)]
    protected Collection $models;

    public function __construct(string $prop1)
    {
        parent::__construct($prop1);

        $this->models = new ArrayCollection();
    }

    /**
     * @return Collection<int,StandardEntity>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(StandardEntity $model): void
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
            $model->setRelation($this);
        }
    }

    public function removeModel(StandardEntity $model): void
    {
        if ($this->models->removeElement($model)) {
            // set the owning side to null (unless already changed)
            if ($model->getRelation() === $this) {
                $model->setRelation(null);
            }
        }
    }
}
