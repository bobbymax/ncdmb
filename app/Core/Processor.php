<?php

namespace App\Core;

use App\DTO\ProcessedIncomingData;
use App\Engine\ControlEngine;
use App\Models\{Document, ProgressTracker, Workflow};
use App\Traits\DocumentFlow;
use Illuminate\Http\Request;
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Facades\{App, File, Log};
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class Processor
{
    use DocumentFlow;
    protected string $serviceNamespace = 'App\\Services\\';
    protected string $repositoryNamespace = 'App\\Repositories\\';
    protected array $classMap = [];
    protected mixed $resolvedService = null;

    public ?Document $document = null;
    public ?Workflow $workflow = null;

    public function __construct()
    {
        $this->loadServices();
        $this->loadRepositories();
    }

    public function __invoke(string $key): static
    {
        // 🔐 First, check if the key is already bound in the container
        if (App::bound($key)) {
            $this->resolvedService = App::make($key);
            return $this;
        }

        // 🧠 Fallback: Guess from convention like 'payment_batch' -> PaymentBatchService
        $className = $this->guessClassFromKey($key);

        if (class_exists($className)) {
            $this->resolvedService = App::make($className);
            return $this;
        }

        throw new InvalidArgumentException("Processor key [$key] could not be resolved.");
    }

    protected function guessClassFromKey(string $key): string
    {
        $isRepo = Str::endsWith($key, 'Repo');
        $base = $isRepo ? Str::replaceLast('Repo', '', $key) : $key;
        $studly = Str::studly($base);

        return $isRepo
            ? "$this->repositoryNamespace{$studly}Repository"
            : "$this->serviceNamespace{$studly}Service";
    }

    protected function loadServices(): void
    {
        $path = app_path('Services');

        foreach (File::files($path) as $file) {
            $name = $file->getFilenameWithoutExtension(); // e.g. ClaimService
            if (Str::endsWith($name, 'Service')) {
                $shortKey = Str::camel(Str::replaceLast('Service', '', $name)); // e.g. claim
                $fqcn = $this->serviceNamespace . $name;
                $this->classMap[$shortKey] = $fqcn;
            }
        }
    }

    protected function loadRepositories(): void
    {
        $path = app_path('Repositories');

        foreach (File::files($path) as $file) {
            $name = $file->getFilenameWithoutExtension(); // e.g. ClaimRepository
            if (Str::endsWith($name, 'Repository')) {
                $shortKey = Str::camel(Str::replaceLast('Repository', '', $name)) . 'Repo'; // e.g. claimRepo
                $fqcn = $this->repositoryNamespace . $name;
                $this->classMap[$shortKey] = $fqcn;
            }
        }
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
        return $this->__invoke($serviceKey)->resolvedService;
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

        return $service->buildDocumentFromTemplate($params);
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

    public function resourceResolver(
        string|int|array $value,
        string $serviceKey,
        array $args = [],
    ): mixed {
        $service = $this->invokeService($serviceKey);

        $column = $args['column'] ?? 'id';  // default to 'id' if not specified
        $category = $args['category'] ?? 'single'; // 'collection' or 'single'

        if (is_numeric($value)) {
            // If value is a single ID
            return $service->show($value);
        }

        if (is_string($value)) {
            // If value is a single string (e.g. search by name, email, etc.)
            if ($category === 'collection') {
                return $service->getCollectionByColumn($column, $value);
            } else {
                return $service->getRecordByColumn($column, $value);
            }
        }

        if (is_array($value)) {
            if (empty($value)) {
                return collect();
            }

            // If arrayed, check if array of numbers or array of strings
            $isNumericArray = collect($value)->every(fn ($v) => is_numeric($v));

            if ($isNumericArray) {
                return $service->whereIn('id', $value);
            } else {
                if (!isset($args['column'])) {
                    throw new \InvalidArgumentException("When passing an array of strings, you must specify a [column] in args.");
                }

                return $service->whereIn($column, $value);
            }
        }

        throw new InvalidArgumentException("Invalid value type passed to resourceResolver.");
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

        $this->document = $this->resourceResolver($data['document_id'], 'document');
        $this->workflow = $this->resourceResolver($data['workflow_id'], 'workflow');

        if (!$this->document || !$this->workflow) {
            throw new \RuntimeException("Invalid document or workflow ID.");
        }
    }

    public function documentBuild(Request $request)
    {

    }

    public function all(): array
    {
        return $this->classMap;
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
