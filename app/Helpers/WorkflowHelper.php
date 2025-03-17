<?php

namespace App\Helpers;

use App\Models\DocumentAction;
use App\Models\Group;
use App\Models\ProgressTracker;
use App\Models\User;
use App\Models\WorkflowStage;

class WorkflowHelper
{
    public static function generateDraftMessage($data): string
    {
        // Fetch user details
        $user = User::find($data['created_by_user_id']);
        $username = $user ? "{$user->surname}, {$user->firstname}" : "Unknown User";

        // Fetch department (Assuming department model exists)
        $department = $user && $user->department ? $user->department->abv : "Unknown Department";

        // Fetch group name
        $group = Group::find($data['group_id']);
        $groupName = $group ? $group->name : "Unknown Group";

        // Fetch workflow stage
        $workflowStage = WorkflowStage::find($data['current_workflow_stage_id']);
        $stageName = $workflowStage ? $workflowStage->name : "Unknown Stage";

        // Fetch document action
        $documentAction = DocumentAction::find($data['document_action_id']);
        $actionName = $documentAction ? $documentAction->action_status : "performed an action";

        // Check if the document is signed
        $isSigned = $data['is_signed'] ? "The document has been signed." : "Signature required: Not yet signed.";

        $ref = $data->document->ref;

        // Generate message based on action performed
        return match (strtolower($actionName)) {
            'passed' => sprintf(
                "%s from %s (Group: %s) has **approved** the document (Ref: %s) at the %s stage.",
                $username, $department, $groupName, $ref, $stageName
            ),
            'failed' => sprintf(
                "%s from %s (Group: %s) has **rejected** the document (Ref: %s) at the %s stage.",
                $username, $department, $groupName, $ref, $stageName
            ),
            'cancelled' => sprintf(
                "%s from %s (Group: %s) has **submitted** the document (Ref: %s) for review at the %s stage.",
                $username, $department, $groupName, $ref, $stageName
            ),
            default => sprintf(
                "%s from %s (Group: %s) has **saved a draft** for the document (Ref: %s) at the %s stage. %s",
                $username, $department, $groupName, $ref, $stageName, $isSigned
            ),
        };
    }
}
