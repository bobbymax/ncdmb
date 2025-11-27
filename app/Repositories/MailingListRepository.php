<?php

namespace App\Repositories;

use App\Models\MailingList;

class MailingListRepository extends BaseRepository
{
    public function __construct(MailingList $mailingList) {
        parent::__construct($mailingList);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'department_id' => $data['department_id'] > 0 ? $data['department_id'] : null,
        ];
    }
}
