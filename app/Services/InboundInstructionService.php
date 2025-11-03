<?php

namespace App\Services;

use App\Models\InboundInstruction;
use App\Repositories\InboundInstructionRepository;
use Illuminate\Support\Facades\DB;

class InboundInstructionService extends BaseService
{
    public function __construct(InboundInstructionRepository $inboundInstructionRepository)
    {
        parent::__construct($inboundInstructionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'inbound_id' => 'required|integer|exists:inbounds,id',
            'instruction_type' => 'required|string|max:255|in:review,respond,forward,approve,archive,other',
            'instruction_text' => 'required|min:3|string',
            'notes' => 'nullable',
            'priority' => 'required|string|max:255|in:low,medium,high,urgent',
            'status' => 'required|string|max:255|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'completion_notes' => 'nullable|string',
            'assignable_id' => 'required|integer',
            'assignable_type' => 'required|string',
            'category' => 'required|string|max:255|in:user,department,group'
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $inboundInstruction = parent::store($data);

            if (!$inboundInstruction) {
                return null;
            }

            // Check if this is the first instruction for this inbound
            if ($inboundInstruction->inbound->instructions()->count() == 1) {
                // Update the inbound document with the first instruction's assignment
                $inboundInstruction->inbound->update([
                    'assignable_id' => $inboundInstruction->assignable_id,
                    'assignable_type' => $inboundInstruction->assignable_type,
                ]);
            }

            return $inboundInstruction;
        });
        
        // Note: Notifications are handled automatically by ResourceNotificationObserver
        // via the NotifiesOnChanges trait on the InboundInstruction model
    }
}
