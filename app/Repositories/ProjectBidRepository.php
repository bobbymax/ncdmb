<?php

namespace App\Repositories;

use App\Models\ProjectBid;

class ProjectBidRepository extends BaseRepository
{
    public function __construct(ProjectBid $projectBid) 
    {
        parent::__construct($projectBid);
    }

    public function parse(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'bid_invitation_id' => $data['bid_invitation_id'],
            'vendor_id' => $data['vendor_id'],
            'bid_reference' => $data['bid_reference'] ?? $this->generateBidReference($data['project_id']),
            'bid_amount' => $data['bid_amount'],
            'bid_currency' => $data['bid_currency'] ?? 'NGN',
            'submitted_at' => $data['submitted_at'] ?? now(),
            'submission_method' => $data['submission_method'] ?? 'physical',
            'received_by' => $data['received_by'] ?? auth()->id(),
            'bid_security_submitted' => $data['bid_security_submitted'] ?? false,
            'bid_security_type' => $data['bid_security_type'] ?? null,
            'bid_security_reference' => $data['bid_security_reference'] ?? null,
            'bid_documents' => $data['bid_documents'] ?? null,
            'status' => $data['status'] ?? 'submitted',
        ];
    }

    private function generateBidReference(int $projectId): string
    {
        $count = $this->model->where('project_id', $projectId)->count() + 1;
        return sprintf('BID/PRJ%d/%04d', $projectId, $count);
    }
}
