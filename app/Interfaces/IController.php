<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface IController
{
    public function index(): \Illuminate\Http\JsonResponse;
    public function store(Request $request): \Illuminate\Http\JsonResponse;
    public function show(int $id): \Illuminate\Http\JsonResponse;
    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse;
    public function destroy(int $id): \Illuminate\Http\JsonResponse;
}
