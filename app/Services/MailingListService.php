<?php

namespace App\Services;

use App\Repositories\MailingListRepository;

class MailingListService extends BaseService
{
    public function __construct(MailingListRepository $mailingListRepository)
    {
        parent::__construct($mailingListRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'group_id' => 'required|integer|min:0',
            'department_id' => 'sometimes|integer|min:0',
            'name' => 'required|string'
        ];
    }
}
