<?php

namespace App\Interfaces;

interface IRepository
{
    public function parse(array $data): array;
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data, bool $parse = true);
    public function destroy(int $id);
    public function generate(string $column, string $prefix);
}
