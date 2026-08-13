<?php

namespace ConLite\System;

class Date
{

    /**
     * @var int Maximum value for a day.
     */
    public const MAX_DAY_VALUE = 31;

    /**
     * @var int Maximum value for a month.
     */
    public const MAX_MONTH_VALUE = 12;

    /**
     * Normalizes a value for the usage as day, ensures to return a two digit representation of a day.
     * - Empty value will return '00'
     * - Values up to '9' will be preceded by a '0', e.g. '09'
     */
    public static function padDay(string $value): string
    {
        return self::_padDayOrMonth($value, self::MAX_DAY_VALUE);
    }

    /**
     * Normalizes a value for the usage as month, ensures to return a two digit representation of a month.
     * - Empty value will return '00'
     * - Values up to '9' will be preceded by a '0', e.g. '09'
     */
    public static function padMonth(string $value): string
    {
        return self::_padDayOrMonth($value, self::MAX_MONTH_VALUE);
    }

    /**
     * Normalizes a value for the usage as day/month, ensures to return a two digit representation of a day/month.
     * Same behaviour as {@see cDate::padDay()}
     */
    public static function padDayOrMonth(string $value): string
    {
        return self::_padDayOrMonth($value, self::MAX_DAY_VALUE);
    }

    /**
     * Checks if passed date string represents an empty date.
     * Following values will be interpreted as empty date:
     * - NULL
     * - '' (empty string)
     * - '0000-00-00'
     * - '0000-00-00 00:00:00'
     *
     * @param string|null|mixed $date
     * @return bool
     */
    public static function isEmptyDate(mixed $date): bool
    {
        return (
            is_null($date) ||
            is_string($date) && (
                empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00'
            )
        );
    }

    /**
     * @param string $value
     * @param int $maxValue
     * @return string
     */
    protected static function _padDayOrMonth(string $value, int $maxValue): string
    {
        $tmpValue = cSecurity::toInteger($value);
        if ($tmpValue < 0 || $tmpValue > $maxValue) {
            return $value;
        }
        return str_pad(trim(cSecurity::toString($value)), 2, '0', STR_PAD_LEFT);
    }
}