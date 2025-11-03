<?php

namespace App\Repositories;

use App\Contracts\ProvidesNotificationRecipients;
use App\Models\InboundInstruction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InboundInstructionRepository extends BaseRepository implements ProvidesNotificationRecipients
{
    public function __construct(InboundInstruction $inboundInstruction) {
        parent::__construct($inboundInstruction);
    }

    public function parse(array $data): array
    {
        return $data;
    }

    /**
     * Resolve notification recipients for inbound instruction
     */
    public function resolveNotificationRecipients(Model $model, string $action): array
    {
        $instruction = $model;
        $recipients = [];
        $assignableType = class_basename($instruction->assignable_type);

        switch ($assignableType) {
            case 'Department':
                $department = \App\Models\Department::find($instruction->assignable_id);
                if ($department) {
                    $ids = array_filter([
                        $department->signatory_staff_id,
                        $department->alternate_signatory_staff_id,
                        $department->director,
                    ]);
                    $recipients = array_values(array_unique(array_filter($ids, fn($id) => $id > 0)));
                }
                break;

            case 'Group':
                $group = \App\Models\Group::find($instruction->assignable_id);
                if ($group) {
                    $recipients = $group->users()->pluck('id')->toArray();
                }
                break;

            case 'User':
                $recipients = [$instruction->assignable_id];
                break;
        }

        // Always add the creator for confirmation
        $recipients[] = $instruction->created_by_id;

        // Remove duplicates and zeros
        return array_values(array_unique(array_filter($recipients, fn($id) => $id > 0)));
    }

    /**
     * Get notification metadata
     */
    public function getNotificationMetadata(Model $model): array
    {
        $instruction = $model;
        
        return [
            'inbound_ref' => $instruction->inbound->ref_no ?? 'N/A',
            'inbound_from' => $instruction->inbound->from_name ?? 'Unknown',
            'inbound_id' => $instruction->inbound_id,
        ];
    }

    /**
     * Get resource data for notification
     */
    public function getNotificationResourceData(Model $model): array
    {
        $instruction = $model;
        
        return [
            'instruction_type' => ucfirst(str_replace('_', ' ', $instruction->instruction_type)),
            'instruction_text' => Str::limit($instruction->instruction_text, 200),
            'priority' => ucfirst($instruction->priority),
            'due_date' => $instruction->due_date ? date('M d, Y', strtotime($instruction->due_date)) : 'Not set',
            'assigned_to' => class_basename($instruction->assignable_type),
            'status' => ucfirst(str_replace('_', ' ', $instruction->status)),
        ];
    }
}
