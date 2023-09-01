<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Persistence;

use Zenstruck\Assert;
use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type Parameters from Factory
 */
final class RepositoryAssertions
{
    /**
     * @internal
     *
     * @param RepositoryDecorator<object> $repository
     */
    public function __construct(private RepositoryDecorator $repository)
    {
    }

    /**
     * @param Parameters $criteria
     */
    public function empty(array $criteria = [], string $message = 'Expected {entity} repository to be empty but it has {actual} items.'): self
    {
        return $this->count(0, $criteria, $message);
    }

    /**
     * @param Parameters $criteria
     */
    public function count(int $expectedCount, array $criteria = [], string $message = 'Expected count of {entity} repository ({actual}) to be {expected}.'): self
    {
        Assert::that($this->repository->count($criteria))
            ->is($expectedCount, $message, ['entity' => $this->repository->getClassName()])
        ;

        return $this;
    }

    /**
     * @param Parameters $criteria
     */
    public function exists(array $criteria, string $message = 'Expected {entity} to exist but it does not.'): self
    {
        Assert::that($this->repository->findOneBy($criteria))->isNotEmpty($message, [
            'entity' => $this->repository->getClassName(),
            'criteria' => $criteria,
        ]);

        return $this;
    }

    /**
     * @param Parameters $criteria
     */
    public function notExists(array $criteria, string $message = 'Expected {entity} to not exist but it does.'): self
    {
        Assert::that($this->repository->findOneBy($criteria))->isEmpty($message, [
            'entity' => $this->repository->getClassName(),
            'criteria' => $criteria,
        ]);

        return $this;
    }
}
