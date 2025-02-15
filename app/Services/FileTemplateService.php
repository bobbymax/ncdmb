<?php

namespace App\Services;

use App\Repositories\FileTemplateRepository;

class FileTemplateService extends BaseService
{
    public function __construct(FileTemplateRepository $fileTemplateRepository)
    {
        parent::__construct($fileTemplateRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'service' => 'required|string|max:255',
            'component' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'repository' => 'required|string|max:255',
            'response_data_format' => 'required|string|max:255',
        ];
    }
}
