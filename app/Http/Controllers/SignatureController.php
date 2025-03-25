<?php

namespace App\Http\Controllers;


use App\Http\Resources\SignatureResource;
use App\Services\SignatureService;

class SignatureController extends BaseController
{
    public function __construct(SignatureService $signatureService) {
        parent::__construct($signatureService, 'Signature', SignatureResource::class);
    }
}
