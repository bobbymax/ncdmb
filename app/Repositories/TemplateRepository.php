<?php

namespace App\Repositories;

use App\Models\Template;

class TemplateRepository extends BaseRepository
{
    public function __construct(Template $template) {
        parent::__construct($template);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'body' => json_encode(array_filter($data['body'], fn($value) => !is_null($value) && $value !== ''))
        ];
    }
}
