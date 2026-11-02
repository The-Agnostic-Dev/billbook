<?php

namespace App\Support;

final class Money
{
    /**
     * Format an amount in naira for display, e.g. "₦12,500.00".
     */
    public static function format(float|int|string|null $amount): string
    {
        $value = round((float) $amount, 2);
        $sign = $value < 0 ? '-' : '';

        return $sign.'₦'.number_format(abs($value), 2);
    }

    /**
     * Turn what a user typed into an amount, accepting things like "₦5000" or "NGN 750.50".
     */
    public static function parse(float|int|string|null $input): ?float
    {
        if ($input === null || trim((string) $input) === '') {
            return null;
        }

        $clean = str_ireplace(['₦', 'NGN', ' '], '', trim((string) $input));

        return round((float) $clean, 2);
    }
}
