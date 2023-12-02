<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Persistence;

use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Persistence\Exception\NotEnoughObjects;

trait RepositoryDecoratorTrait
{

    public function assert(): RepositoryAssertions
    {
        return new RepositoryAssertions($this);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public function random(array $criteria = []): object
    {
        return $this->randomSet(1, $criteria)[0];
    }

    /**
     * @param positive-int $count
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public function randomSet(int $count, array $criteria = []): array
    {
        if ($count < 1) {
            throw new \InvalidArgumentException(\sprintf('$number must be positive (%d given).', $count));
        }

        return $this->randomRange($count, $count, $criteria);
    }

    /**
     * @param positive-int $min
     * @param positive-int $max
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public function randomRange(int $min, int $max, array $criteria = []): array
    {
        if ($min < 1) {
            throw new \InvalidArgumentException(\sprintf('$min must be positive (%d given).', $min));
        }

        if ($max < $min) {
            throw new \InvalidArgumentException(\sprintf('$max (%d) cannot be less than $min (%d).', $max, $min));
        }

        $all = \array_values($this->findBy($criteria));

        \shuffle($all);

        if (\count($all) < $max) {
            throw new NotEnoughObjects(\sprintf('At least %d "%s" object(s) must have been persisted (%d persisted).', $max, $this->getClassName(), \count($all)));
        }

        return \array_slice($all, 0, \random_int($min, $max)); // @phpstan-ignore-line
    }
    /**
     * @param Parameters $criteria
     *
     * @return Parameters
     */
    private function normalize(array $criteria): array
    {
        $normalized = [];

        foreach ($criteria as $key => $value) {
            if ($value instanceof Factory) {
                // create factories
                $value = $value instanceof PersistentObjectFactory ? $value->withoutPersisting()->create() : $value->create();
            }

            if ($value instanceof Proxy) {
                // unwrap proxies
                $value = $value->_real();
            }

            if (!\is_object($value) || null === $embeddableProps = Configuration::instance()->persistence()->embeddablePropertiesFor($value, $this->getClassName())) {
                $normalized[$key] = $value;

                continue;
            }

            // expand embeddables
            foreach ($embeddableProps as $subKey => $subValue) {
                $normalized["{$key}.{$subKey}"] = $subValue;
            }
        }

        return $normalized;
    }
}
