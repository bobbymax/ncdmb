<?php

namespace App\Services;

use App\Repositories\TemplateRepository;

class TemplateService extends BaseService
{
    public function __construct(TemplateRepository $templateRepository)
    {
        parent::__construct($templateRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'name' => 'required|string|max:255',
            'header' => 'required|string|max:255',
            'body' => 'sometimes|array',
            'footer' => 'required|string|max:255',
            'active' => 'required',
        ];
    }
}
