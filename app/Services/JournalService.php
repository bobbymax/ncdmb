<?php

namespace App\Services;

use App\Repositories\JournalRepository;

class JournalService extends BaseService
{
    public function __construct(JournalRepository $journalRepository)
    {
        parent::__construct($journalRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
