<?php

namespace App\Notifications;


use App\DTO\DocumentActivityContext;

class MessageBuilder
{
    public function subject(DocumentActivityContext $ctx, array $tags): string
    {
        // Examples; customize by action, stage, tags
        return match ($ctx->action_performed) {
            'create'   => "[{$ctx->service}] {$ctx->document_title} generated",
            'submit'   => "[{$ctx->service}] {$ctx->document_title} submitted to {$ctx->workflow_stage_id}",
            'approve'  => "[{$ctx->service}] {$ctx->document_title} approved",
            'query'    => "[{$ctx->service}] {$ctx->document_title} queried",
            'signed'    => "[{$ctx->service}] {$ctx->document_title} signed",
            default    => "[{$ctx->service}] {$ctx->document_title} updated",
        };
    }

    public function lines(DocumentActivityContext $ctx, array $tags): array
    {
        $lines = [];

        if (in_array('owner', $tags)) {
            $lines[] = "A document was created on your behalf by **{$ctx->loggedInUser['name']}**.";
        }

        if (in_array('creator_ack', $tags)) {
            $lines[] = "You created **{$ctx->document_title}** for **{$ctx->document_owner['label']}**.";
        }

        if (in_array('self_created', $tags)) {
            $lines[] = "You created **{$ctx->document_title}**.";
        }

        if (in_array('watcher', $tags) || in_array('watcher_group', $tags)) {
            $lines[] = "You’re receiving this as a **watcher**.";
        }

        // Action/Stage-specific context
        $lines[] = "Action: **{$ctx->action_performed}** at stage **{$ctx->workflow_stage_id}**.";
        if ($ctx->document_ref) {
            $lines[] = "Reference: **{$ctx->document_ref}**.";
        }

        // Optional tracker details if helpful
        if ($ctx->pointer) {
            $lines[] = "Tracker: #{$ctx->pointer['id']} (status: {$ctx->pointer['status']}).";
        }

        $lines[] = "Open in portal to view details and next steps.";

        return $lines;
    }
}
