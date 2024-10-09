<?php

namespace App\Http\Controllers;

use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Handlers\ValidationErrors;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    use ApiResponse;
    protected $service;
    protected $name;

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->success($this->service->index());
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 422);
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), $this->service->rules());

            if ($validator->fails()) {
                $error = new ValidationErrors($validator->errors());
                return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
            }

            $resource = $this->service->store($request->except('id'));
            return $this->success($resource, $this->stored($this->name), 201);

        } catch (RecordCreationUnsuccessful $e) {

            return $this->error(null, $e->getMessage(), 422);

        } catch (\Exception $e) {

            return $this->error(null, 'Server Error', 500);

        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->success($this->service->show((int) $id));
        } catch (DataNotFound $e) {
            return $this->error(null, $e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error(null, 'Server Error', 500);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), $this->service->rules());

            if ($validator->fails()) {
                $error = new ValidationErrors($validator->errors());
                return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
            }

            $updated = $this->service->update((int) $id, $request->except('id'));
            return $this->success($updated, $this->updated($this->name));

        } catch (DataNotFound|\Exception $e) {
            return $this->error(null, $e->getMessage(), 422);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->service->destroy((int) $id);
            return $this->success(null, $this->destroyed($this->name));
        } catch (DataNotFound $e) {
            return $this->error(null, $e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error(null, 'Server Error', 500);
        }
    }
}
