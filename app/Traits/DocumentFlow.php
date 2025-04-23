<?php

namespace App\Traits;

use App\Engine\Puzzle;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentType;
use App\Models\Entity;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Workflow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait DocumentFlow
{
    protected function getFirstTrackerId(int $workflowId): int
    {
        $workflow = $this->getWorkflow($workflowId);

        if (!$workflow) {
            return 0;
        }

        $tracker = $workflow->trackers()->where('order', 1)->first();

        return $tracker?->id ?? 0;
    }

    protected function getWorkflow(int $workflowId)
    {
        return Workflow::find($workflowId);
    }

    protected function getCreationDocumentAction(): DocumentAction
    {
        return DocumentAction::where('label', 'create-resource')->first();
    }

    protected function getDocumentAction(int $id): DocumentAction
    {
        return DocumentAction::find($id);
    }

    protected function getDocumentType(string $docTypeLabel)
    {
        return DocumentType::where('label', $docTypeLabel)->first();
    }

    protected function getDocument(int $id)
    {
        return Document::find($id);
    }

    protected function signatureUpload(string $dataUrl): string
    {
        if ($dataUrl === "") {
            return "";
        }

        $fileData = explode(',', $dataUrl);
        $decodedData = base64_decode($fileData[1]);
        $fileName = uniqid() . '.png';

        Storage::disk('public')->put("signatures/$fileName", $decodedData);

        return "signatures/$fileName";
    }

    protected function deleteFile(string $filePath): bool
    {
        return Storage::disk('public')->delete($filePath);
    }

    public function scramble($content, $user): string
    {
        return Puzzle::scramble($content, $user->staff_no);
    }

    public function resolveFileContent($content): string
    {
        return Puzzle::resolve($content);
    }

    protected function workflowArgs(
        string $serviceClass,
        int $actionId,
        mixed $resource,
        ?string $amount = null,
        ?string $signature = null,
        ?string $message = null,
    ): array {
        return [
            'service' => $serviceClass,
            'document_action_id' => $actionId,
            'serverState' => $this->setStateValues($resource->id),
            'signature' => $signature,
            'message' => $message,
            'amount' => $amount
        ];
    }

    protected function setStateValues(int $resourceId): array
    {
        return [
            'resource_id' => $resourceId,
            'is_signed' => false,
        ];
    }

    protected function beneficiary(int $entityId, string $entity_type = "staff")
    {
        return match ($entity_type) {
            "entity" => Entity::find($entityId),
            "vendor" => Vendor::find($entityId),
            default => User::find($entityId),
        };
    }
}
