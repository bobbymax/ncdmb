<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class NotificationTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notification:templates {action=list : Action to perform (list, create, test)} {--type= : Template type for create/test actions}';

    /**
     * The console command description.
     */
    protected $description = 'Manage notification markdown templates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listTemplates(),
            'create' => $this->createTemplate(),
            'test' => $this->testTemplate(),
            default => $this->error('Invalid action. Use: list, create, or test')
        };
    }

    /**
     * List all available templates
     */
    protected function listTemplates(): int
    {
        $templateDir = resource_path('views/notifications/workflow');
        
        if (!File::exists($templateDir)) {
            $this->error('Template directory does not exist: ' . $templateDir);
            return 1;
        }

        $templates = File::files($templateDir);
        
        if (empty($templates)) {
            $this->warn('No templates found in: ' . $templateDir);
            return 0;
        }

        $this->info('Available notification templates:');
        $this->newLine();

        foreach ($templates as $template) {
            $name = $template->getFilenameWithoutExtension();
            $size = $template->getSize();
            $modified = date('Y-m-d H:i:s', $template->getMTime());
            
            $this->line("  • {$name}.md ({$size} bytes, modified: {$modified})");
        }

        return 0;
    }

    /**
     * Create a new template
     */
    protected function createTemplate(): int
    {
        $type = $this->option('type');
        
        if (!$type) {
            $type = $this->ask('Enter template type (e.g., pending_action, acknowledgment, status_update, document_action)');
        }

        if (!$type) {
            $this->error('Template type is required');
            return 1;
        }

        $templatePath = resource_path("views/notifications/workflow/{$type}.md");
        
        if (File::exists($templatePath)) {
            if (!$this->confirm("Template {$type}.md already exists. Overwrite?")) {
                $this->info('Template creation cancelled');
                return 0;
            }
        }

        $template = $this->getDefaultTemplate($type);
        File::put($templatePath, $template);

        $this->info("Template created: {$templatePath}");
        return 0;
    }

    /**
     * Test a template
     */
    protected function testTemplate(): int
    {
        $type = $this->option('type');
        
        if (!$type) {
            $type = $this->ask('Enter template type to test');
        }

        if (!$type) {
            $this->error('Template type is required');
            return 1;
        }

        $templatePath = resource_path("views/notifications/workflow/{$type}.md");
        
        if (!File::exists($templatePath)) {
            $this->error("Template not found: {$templatePath}");
            return 1;
        }

        try {
            $service = app(\App\Services\NotificationTemplateService::class);
            $template = $service->getTemplate($type);
            
            $this->info("Template loaded successfully:");
            $this->newLine();
            $this->line("Subject: " . $template['subject']);
            $this->line("Greeting: " . $template['greeting']);
            $this->line("Body: " . $template['body']);
            $this->line("Action Text: " . $template['action_text']);
            $this->line("Action URL: " . $template['action_url']);
            $this->line("Footer: " . $template['footer']);

        } catch (\Exception $e) {
            $this->error("Error testing template: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Get default template content
     */
    protected function getDefaultTemplate(string $type): string
    {
        $templates = [
            'pending_action' => "# Document Awaiting Action - {{ document_ref }}\n\nHello {{ recipient_name }},\n\nDocument **{{ document_ref }}** ({{ document_title }}) is now awaiting your action at **{{ tracker_name }}**. Please review and take the necessary action.\n\n[Take Action]({{ document_url }})\n\n---\n\nPlease process this document as soon as possible.",
            'acknowledgment' => "# Action Acknowledged - {{ document_ref }}\n\nHello {{ recipient_name }},\n\nThe action you performed on document **{{ document_ref }}** ({{ document_title }}) has been acknowledged and the document has moved to the next stage (**{{ tracker_name }}**).\n\n[View Document]({{ document_url }})\n\n---\n\nThank you for your action on this document.",
            'status_update' => "# Document Status Update - {{ document_ref }}\n\nHello {{ recipient_name }},\n\nDocument **{{ document_ref }}** ({{ document_title }}) has been updated with status **'{{ action_status }}'** at **{{ tracker_name }}**. Please review the current status.\n\n[View Document]({{ document_url }})\n\n---\n\nThis is a status update notification for the current stage.",
            'document_action' => "# Document Action Notification - {{ document_ref }}\n\nHello {{ recipient_name }},\n\nDocument **{{ document_ref }}** ({{ document_title }}) has been processed by **{{ logged_in_user_name }}**. You are receiving this notification as a watcher of this document.\n\n[View Document]({{ document_url }})\n\n---\n\nThis is an informational notification about document activity.",
        ];

        return $templates[$type] ?? "# Document Notification - {{ document_ref }}\n\nHello {{ recipient_name }},\n\nThere has been activity on document **{{ document_ref }}** ({{ document_title }}).\n\n[View Document]({{ document_url }})\n\n---\n\nPlease check the document for more details.";
    }
}