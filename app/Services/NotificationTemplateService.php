<?php

namespace App\Services;

use App\Interfaces\NotificationTemplateServiceInterface;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class NotificationTemplateService implements NotificationTemplateServiceInterface
{
    /**
     * Get notification template for a specific type
     */
    public function getTemplate(string $type): array
    {
        $templatePath = "notifications.workflow.{$type}";

        // Check if markdown template exists
        if (View::exists($templatePath)) {
            return $this->parseMarkdownTemplate($templatePath);
        }

        // Fallback to config templates
        $templates = config('notifications.workflow.templates', []);
        return $templates[$type] ?? $this->getDefaultTemplate();
    }

    /**
     * Get channels for a recipient type
     */
    public function getChannelsForRecipientType(string $type): array
    {
        return match ($type) {
            'current_tracker' => ['mail', 'database'],
            default => ['database']
        };
    }

    /**
     * Get queue for a recipient type
     */
    public function getQueueForRecipientType(string $type): string
    {
        return match ($type) {
            'current_tracker' => 'notifications-high',
            'previous_tracker' => 'notifications-medium',
            'watchers' => 'notifications-low',
            default => 'notifications'
        };
    }

    /**
     * Get priority for a recipient type
     */
    public function getPriorityForRecipientType(string $type): string
    {
        return match ($type) {
            'current_tracker' => 'high',
            'previous_tracker' => 'medium',
            default => 'low'
        };
    }

    /**
     * Process template with context variables
     */
    public function processTemplate(string $type, array $context): array
    {
        $template = $this->getTemplate($type);
        $variables = $context['template_variables'] ?? [];

        $processed = [];
        foreach ($template as $key => $value) {
            $processed[$key] = $this->interpolateString($value, $variables);
        }

        return $processed;
    }

    /**
     * Parse markdown template and extract components
     */
    protected function parseMarkdownTemplate(string $templatePath): array
    {
        $markdown = View::make($templatePath)->render();

        // Extract title (first # heading)
        preg_match('/^#\s+(.+)$/m', $markdown, $titleMatches);
        $title = $titleMatches[1] ?? 'Document Notification';

        // Extract greeting (Hello ... line)
        preg_match('/^Hello\s+(.+),$/m', $markdown, $greetingMatches);
        $greeting = $greetingMatches[0] ?? 'Hello,';

        // Extract body (content between greeting and first link)
        $bodyStart = strpos($markdown, $greeting) + strlen($greeting) + 1;
        $linkStart = strpos($markdown, '[', $bodyStart);
        $body = trim(substr($markdown, $bodyStart, $linkStart - $bodyStart));

        // Extract action link
        preg_match('/\[([^\]]+)\]\(([^)]+)\)/', $markdown, $linkMatches);
        $actionText = $linkMatches[1] ?? 'View Document';
        $actionUrl = $linkMatches[2] ?? '#';

        // Extract footer (content after ---)
        $footerStart = strpos($markdown, '---');
        $footer = $footerStart !== false ? trim(substr($markdown, $footerStart + 3)) : '';

        return [
            'subject' => $title,
            'greeting' => $greeting,
            'body' => $body,
            'action_text' => $actionText,
            'action_url' => $actionUrl,
            'footer' => $footer,
        ];
    }

    /**
     * Interpolate string with variables
     */
    protected function interpolateString(string $string, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $string = str_replace("{{$key}}", $value, $string);
        }

        return $string;
    }

    /**
     * Get default template
     */
    protected function getDefaultTemplate(): array
    {
        return [
            'subject' => 'Document Notification - {document_ref}',
            'greeting' => 'Hello {recipient_name},',
            'body' => 'There has been activity on document {document_ref} ({document_title}).',
            'action_text' => 'View Document',
            'action_url' => '{document_url}',
            'footer' => 'Please check the document for more details.',
        ];
    }

    /**
     * Check if a channel is enabled globally
     */
    public function isChannelEnabled(string $channel): bool
    {
        $global = config('notifications.global', []);

        return match ($channel) {
            'mail' => $global['enable_email'] ?? true,
            'sms' => $global['enable_sms'] ?? false,
            'database' => $global['enable_database'] ?? true,
            default => false
        };
    }

    /**
     * Get retry configuration
     */
    public function getRetryConfig(): array
    {
        return config('notifications.workflow.retry', [
            'tries' => 3,
            'backoff' => [5, 15, 30],
            'timeout' => 60,
        ]);
    }
}
