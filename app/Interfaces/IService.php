<?php

namespace App\Interfaces;

interface IService
{
    public function rules();
    public function index();
    public function store(array $data);
    public function show(int $id);
    public function update(int $id, array $data, $parsed = true);
    public function destroy(int $id);
}
