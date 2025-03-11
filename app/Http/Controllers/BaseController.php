<?php

namespace App\Http\Controllers;

use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Handlers\ValidationErrors;
use App\Interfaces\IController;
use App\Services\BaseService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class BaseController extends Controller implements IController
{
    use ApiResponse;
    protected BaseService $service;
    protected string $name;
    protected string $jsonResource;

    public function __construct(BaseService $service, string $name, string $jsonResource)
    {
        $this->service = $service;
        $this->name = $name;
        $this->jsonResource = $jsonResource;
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->success($this->jsonResource::collection($this->service->index()));
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
            return $this->success(new $this->jsonResource($resource), $this->stored($this->name), 201);

        } catch (RecordCreationUnsuccessful $e) {

            return $this->error(null, $e->getMessage(), 422);

        } catch (\Exception $e) {

            return $this->error(null, $e->getMessage(), 500);

        }
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        try {
            return $this->success(new $this->jsonResource($this->service->show((int) $id)));
        } catch (DataNotFound $e) {
            return $this->error(null, $e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), $this->service->rules('update'));

            if ($validator->fails()) {
                $error = new ValidationErrors($validator->errors());
                return $this->error($error->getValidationErrors(), $error->getMessage(), 422);
            }

            $updated = $this->service->update((int) $id, $request->except('id'));
            return $this->success(new $this->jsonResource($updated), $this->updated($this->name));

        } catch (\Exception $e) {
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
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
