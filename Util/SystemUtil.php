<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\System\Util;

use Klipper\Component\System\Exception\RuntimeException;

/**
 * System Utils.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class SystemUtil
{
    private static ?int $memoryLimit = null;

    /**
     * Convert the human representation of size to bytes.
     *
     * @param string $from The value
     */
    public static function convertToBytes(string $from): int
    {
        if (is_numeric($from)) {
            return (int) $from;
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $simpleUnits = ['B', 'K', 'M', 'G', 'T', 'P'];
        preg_match('/^(\d+)(.+)$/', $from, $matches);

        if (empty($matches)) {
            throw new RuntimeException(sprintf(
                'The value "%s" is not a human representation of size',
                $from
            ));
        }

        $number = (int) $matches[1];
        $suffix = trim($matches[2]);
        $exponent = array_flip($units)[strtoupper($suffix)] ?? null;

        if (null === $exponent) {
            $exponent = array_flip($simpleUnits)[strtoupper($suffix)] ?? null;
        }

        if (null === $exponent) {
            throw new RuntimeException(sprintf(
                'The unit "%s" does not exist for "%s". Only available: %s',
                $suffix,
                $from,
                implode(', ', $units)
            ));
        }

        return $number * (1024 ** $exponent);
    }

    /**
     * Get the memory limit in byte.
     */
    public static function getMemoryLimit(): int
    {
        if (null === self::$memoryLimit) {
            self::$memoryLimit = static::convertToBytes(trim(\ini_get('memory_limit')));
        }

        return self::$memoryLimit;
    }

    /**
     * Get the memory usage in byte.
     */
    public static function getMemoryUsage(): int
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Check if the script is out of memory limit.
     */
    public static function isOutOfMemoryLimit(): bool
    {
        return self::getMemoryUsage() >= self::getMemoryLimit();
    }

    /**
     * Validate the size of system memory.
     *
     * @throws RuntimeException When the execution is out of the memory limit
     */
    public static function validateMemory(): void
    {
        if (self::isOutOfMemoryLimit()) {
            throw new RuntimeException('Execution is out of the memory limit. Increase the limit of memory.');
        }
    }
}
