<?php

namespace App\Interfaces;

interface Importable
{
    public function import(array $rows): array;
}
