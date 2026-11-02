<?php

use App\Support\Money;

if (! function_exists('naira')) {
    function naira(float|int|string|null $amount): string
    {
        return Money::format($amount);
    }
}
