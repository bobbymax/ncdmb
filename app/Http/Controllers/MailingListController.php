<?php

namespace App\Http\Controllers;


use App\Http\Resources\MailingListResource;
use App\Services\MailingListService;

class MailingListController extends BaseController
{
    public function __construct(MailingListService $mailingListService) {
        parent::__construct($mailingListService, 'MailingList', MailingListResource::class);
    }
}
