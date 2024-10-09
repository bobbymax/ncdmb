<?php

namespace App\Interfaces;

interface IRepository
{
    public function parse(array $data): array;
    public function all();
    public function find($id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function destroy($id);
    public function generate($column, $prefix);
}
