<?php

namespace App\Services;

use App\Models\Inbound;
use App\Repositories\InboundRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboundService extends BaseService
{
    protected UploadRepository $uploadRepository;
    public function __construct(InboundRepository $inboundRepository, UploadRepository $uploadRepository)
    {
        parent::__construct($inboundRepository);
        $this->uploadRepository = $uploadRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'from_name' => 'required|string|max:255',
            'from_email' => 'nullable|string|email|max:255',
            'from_phone' => 'nullable|string|max:255',
            'file_uploads' => 'required|array',
            'security_class' => 'required|string|in:public,internal,confidential,secret',
            'channel' => 'required|string|in:hand_delivery,post,email,courier,other',
            'priority' => 'required|string|in:low,medium,high',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $inbound = parent::store([
                ...$data,
                'ref_no' => $this->generate('ref_no', 'INB'),
                'received_by_id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
                'mailed_at' => now(),
                'authorising_officer_id' => null,
                'vendor_id' => null,
            ]);

            if (!$inbound) {
                return null;
            }

            $this->uploadRepository->uploadMany($data['file_uploads'], $inbound->id, Inbound::class);

            return $inbound;
        });
    }
}
