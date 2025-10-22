<?php

namespace App\Core;

use App\Core\Contracts\ResourceResolverInterface;
use App\Core\Contracts\ServiceResolverInterface;
use App\Core\Contracts\WorkflowProcessorInterface;
use App\DTO\ProcessedIncomingData;
use App\Engine\ControlEngine;
use App\Models\{Document, DocumentAction, ProgressTracker, Workflow};
use App\Traits\DocumentFlow;
use Illuminate\Http\Request;
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Facades\{App, File, Log};
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class Processor
{
    use DocumentFlow;
    
    private ServiceResolverInterface $serviceResolver;
    private ResourceResolverInterface $resourceResolver;
    private WorkflowProcessorInterface $workflowProcessor;
    
    protected mixed $resolvedService = null;
    public ?Document $document = null;
    public ?Workflow $workflow = null;

    public function __construct(
        ServiceResolverInterface $serviceResolver,
        ResourceResolverInterface $resourceResolver,
        WorkflowProcessorInterface $workflowProcessor
    ) {
        $this->serviceResolver = $serviceResolver;
        $this->resourceResolver = $resourceResolver;
        $this->workflowProcessor = $workflowProcessor;
    }

    public function __invoke(string $key): static
    {
        $this->resolvedService = $this->serviceResolver->resolve($key);
        return $this;
    }

    public function getResolvedService(): mixed
    {
        return $this->resolvedService;
    }


    private function preProcessor(Request $request): array
    {
        $serviceKey = $request->input('service');
        $method = $request->input('method');
        $mode = $request->input('mode');
        $args = $request->input('args', []);

        if (!$serviceKey) {
            throw new InvalidArgumentException("Missing [service].");
        }

        $this->verifyWorkflowContext($request);

        return compact('serviceKey', 'method', 'mode', 'args');
    }

    private function invokeService($serviceKey): mixed
    {
        return $this->serviceResolver->resolve($serviceKey);
    }

    private function resolveMethod($service, $method, $mode): ?string
    {
        return method_exists($service, $method)
            ? $method
            : ($mode === 'store' && method_exists($service, 'store') ? 'store'
            : ($mode === 'update' && method_exists($service, 'update') ? 'update' : null));
    }

    public function processCollection(string $serviceRequest, array $params)
    {
        $service = $this->invokeService($serviceRequest);

        $actualMethod = $this->resolveMethod($service, 'collection', 'index');

        if (!$actualMethod || !method_exists($service, $actualMethod)) {
            throw new \BadMethodCallException("Neither method nor fallback [update] exist on service.");
        }

        return call_user_func_array([$service, $actualMethod], $params);
    }

    public function saveResource(array $params, bool $isUpdate)
    {
        $service = $this->invokeService($params['service']);
        $actualMethod = $this->resolveMethod($service, 'buildDocumentFromTemplate', $isUpdate ? 'update' : 'store');

        if (!$actualMethod || !method_exists($service, $actualMethod)) {
            throw new \BadMethodCallException("Neither method nor fallback [update] exist on service.");
        }

        return $service->buildDocumentFromTemplate($params, $isUpdate);
    }

    /**
     * @throws ValidationException
     */
    public function handleFrontendRequest(Request $request): mixed
    {
        // Collect Document keys
        $documentAttributes = $this->preProcessor($request);

        $service = $this->invokeService($documentAttributes['serviceKey']);
        $actualMethod = $this->resolveMethod(
            $service,
            $documentAttributes['method'],
            $documentAttributes['mode']
        );

        if (!$actualMethod || !method_exists($service, $actualMethod)) {
            throw new \BadMethodCallException("Neither method [{$documentAttributes['method']}] nor fallback [update] exist on service.");
        }

        $payload = ProcessedIncomingData::from($request->all());

        return new TaskProcessor(
            get_class($service),
            $actualMethod,
            $payload,
            Arr::wrap($documentAttributes['args']),
        );
    }

    public function executeWorkflowAction(string $action, Document $document, ?DocumentAction $documentAction = null): mixed
    {
        return $this->workflowProcessor->executeAction($action, $document, $documentAction);
    }

    public function resourceResolver(
        string|int|array $value,
        string $serviceKey,
        array $args = [],
    ): mixed {
        return $this->resourceResolver->resolve($value, $serviceKey, $args);
    }

    protected function currentTracker()
    {
        if (!empty($this->document->drafts)) {
            $draft = $this->document->drafts()->latest()->first();
            return ProgressTracker::find($draft->progress_tracker_id);
        }

        return $this->workflow->trackers()->firstWhere('order', 1);
    }

    /**
     * @throws \ReflectionException
     */
    protected function resolveRules($service, string $method): array
    {
        $reflection = new \ReflectionMethod($service, 'rules');
        return $reflection->getNumberOfParameters() > 0
            ? $service->rules($method)
            : $service->rules();
    }

    protected function verifyWorkflowContext(array|Request $input): void
    {
        $data = $input instanceof Request ? $input->all() : $input;

        foreach ([
            'workflow_id', 'document_id', 'document_draft_id', 'mode', 'document_action_id', 'progress_tracker_id', 'resources'
                 ] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException("Missing required field: {$key}");
            }
        }

        $this->document = $this->resourceResolver->resolve($data['document_id'], 'document');
        $this->workflow = $this->resourceResolver->resolve($data['workflow_id'], 'workflow');

        if (!$this->document || !$this->workflow) {
            throw new \RuntimeException("Invalid document or workflow ID.");
        }
    }

    public function all(): array
    {
        return $this->serviceResolver->getServiceMap();
    }

    public function trigger(
        mixed $service,
        Document $document,
        array $args = []
    ): void {
        $engine = app(ControlEngine::class);
        $action = isset($args['document_action_id']) && $args['document_action_id']
            ? $this->getDocumentAction($args['document_action_id'])
            : $this->getCreationDocumentAction();

        $engine->initialize(
            app($service),
            $document,
            $document->workflow,
            $document->current_tracker,
            $action,
            $args['serverState'] ?? [],
            $args['signature'] ?? null,
            $args['message'] ?? null,
            $args['amount'] ?? null,
            "trigger"
        );

        $engine->process();
    }
}
