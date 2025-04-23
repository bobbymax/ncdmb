<?php

if (!function_exists('processor')) {
    function processor(?string $key = null)
    {
        $resolver = app('processor');

        return $key ? $resolver($key) : $resolver;
    }
}
