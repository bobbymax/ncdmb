<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentCommentResource;
use App\Services\DocumentCommentService;

class DocumentCommentController extends BaseController
{
    public function __construct(DocumentCommentService $documentCommentService) {
        parent::__construct($documentCommentService, 'DocumentComment', DocumentCommentResource::class);
    }
}
