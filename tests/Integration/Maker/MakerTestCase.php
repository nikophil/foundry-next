<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Maker;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class MakerTestCase extends KernelTestCase
{
    /**
     * @before
     */
    public static function cleanupTempDir(): void
    {
        (new Filesystem())->remove(self::tempDir());
    }

    protected static function tempDir(): string
    {
        return __DIR__.'/../../Fixture/Maker';
    }

    protected static function tempFile(string $path): string
    {
        return \sprintf('%s/%s', self::tempDir(), $path);
    }
}
