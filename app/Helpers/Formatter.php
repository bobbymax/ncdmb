<?php

namespace App\Helpers;

class Formatter
{
    public static function currency($amount): bool|string
    {
        return numfmt_format_currency(numfmt_create('en_NG', \NumberFormatter::CURRENCY), $amount, "NGN");
    }
}
