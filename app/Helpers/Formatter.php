<?php

namespace App\Helpers;

class Formatter
{
    public static function currency($amount, string $currency = "NGN"): bool|string
    {
        $formatter = numfmt_create('en_NG', \NumberFormatter::CURRENCY);
        return numfmt_format_currency($formatter, $amount, $currency);
    }
}
