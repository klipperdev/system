<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\System\Tests\Util;

use Klipper\Component\System\Exception\RuntimeException;
use Klipper\Component\System\Util\SystemUtil;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @group klipper
 * @group klipper-system
 * @group klipper-system-util
 *
 * @internal
 */
final class SystemUtilTest extends TestCase
{
    private string $previousMemoryLimit;

    protected function setUp(): void
    {
        $this->previousMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '1G');
    }

    protected function tearDown(): void
    {
        ini_set('memory_limit', $this->previousMemoryLimit);
    }

    public function getConvertToBytesData(): array
    {
        return [
            [42, '42'],
            [42, '42B'],
            [42, '42 B'],
            [43008, '42KB'],
            [43008, '42 KB'],
            [43008, '42Kb'],
            [43008, '42 Kb'],
            [43008, '42kb'],
            [43008, '42 kb'],
            [43008, '42K'],
            [43008, '42 K'],
            [44040192, '42MB'],
            [44040192, '42 MB'],
            [44040192, '42M'],
            [44040192, '42 M'],
            [45097156608, '42GB'],
            [45097156608, '42 GB'],
            [45097156608, '42G'],
            [45097156608, '42 G'],
            [46179488366592, '42TB'],
            [46179488366592, '42 TB'],
            [46179488366592, '42T'],
            [46179488366592, '42 T'],
            [47287796087390208, '42PB'],
            [47287796087390208, '42 PB'],
            [47287796087390208, '42P'],
            [47287796087390208, '42 P'],
        ];
    }

    /**
     * @dataProvider getConvertToBytesData
     *
     * @param int        $expected
     * @param int|string $value
     */
    public function testConvertToBytes(int $expected, $value): void
    {
        static::assertSame($expected, SystemUtil::convertToBytes($value));
    }

    public function getInvalidHumanRepresentationData(): array
    {
        return [
            ['A'],
            ['AB'],
            ['AKB'],
            ['A B'],
            ['A KB'],
        ];
    }

    /**
     * @dataProvider getInvalidHumanRepresentationData
     *
     * @param string $value
     */
    public function testInvalidHumanRepresentation(string $value): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The value "'.$value.'" is not a human representation of size');

        SystemUtil::convertToBytes($value);
    }

    public function getInvalidUnitData(): array
    {
        return [
            ['42.5B'],
            ['42.5KB'],
            ['42.5 B'],
            ['42.5 KB'],
            ['42 U'],
        ];
    }

    /**
     * @dataProvider getInvalidUnitData
     *
     * @param string $value
     */
    public function testInvalidUnit(string $value): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/The unit "([\w\d. ]+)" does not exist for "'.$value.'". Only available: B, KB, MB, GB, TB, PB/');

        SystemUtil::convertToBytes($value);
    }

    public function testGetMemoryLimit(): void
    {
        static::assertGreaterThan(0, SystemUtil::getMemoryLimit());
    }

    public function testGetMemoryUsage(): void
    {
        static::assertGreaterThan(0, SystemUtil::getMemoryUsage());
    }

    public function testIsOutOfMemoryLimit(): void
    {
        static::assertFalse(SystemUtil::isOutOfMemoryLimit());
    }
}
