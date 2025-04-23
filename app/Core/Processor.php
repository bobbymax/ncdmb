<?php

namespace App\Core;

use App\Engine\ControlEngine;
use App\Models\Document;
use App\Models\ProgressTracker;
use App\Models\Workflow;
use App\Traits\DocumentFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Processor
{
    use DocumentFlow;
    protected string $serviceNamespace = 'App\\Services\\';
    protected string $repositoryNamespace = 'App\\Repositories\\';
    protected array $classMap = [];
    public ?Document $document = null;
    public ?Workflow $workflow = null;

    public function __construct()
    {
        $this->loadServices();
        $this->loadRepositories();
    }

    public function __invoke(string $key)
    {
        // 🔐 First, check if the key is already bound in the container
        if (App::bound($key)) {
            return App::make($key);
        }

        // 🧠 Fallback: Guess from convention like 'payment_batch' -> PaymentBatchService
        $className = $this->guessServiceOrRepo($key);

        if (class_exists($className)) {
            return App::make($className);
        }

        throw new \InvalidArgumentException("Processor key [{$key}] could not be resolved.");
    }

    protected function guessServiceOrRepo(string $key): ?string
    {
        $normalized = Str::studly(Str::replaceLast('Repo', '', $key));

        if (Str::endsWith($key, 'Repo')) {
            return "App\\Repositories\\{$normalized}Repository";
        }

        return "App\\Services\\{$normalized}Service";
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

    /**
     * @throws ValidationException
     * @throws \ReflectionException
     */
    public function handleFrontendRequest(Request $request): mixed
    {
        $serviceKey = $request->input('service');
        $method = $request->input('method');
        $mode = $request->input('mode');
        $args = $request->input('args', []);

        if (!$serviceKey || !$method) {
            throw new \InvalidArgumentException("Missing [service] or [method].");
        }

        $this->verifyWorkflowContext($request);

        $service = $this->__invoke($serviceKey)->resolvedService;
        $actualMethod = method_exists($service, $method)
            ? $method
            : ($mode === 'store' && method_exists($service, 'store') ? 'store'
                : ($mode === 'update' && method_exists($service, 'update') ? 'update' : null));

        if (!method_exists($service, $actualMethod)) {
            throw new \BadMethodCallException("Neither method [{$method}] nor fallback [update] exist on service.");
        }

        $validated = method_exists($service, 'rules')
            ? Validator::make($request->all(), $this->resolveRules($service, $method))->validate()
            : $request->all();

        return call_user_func_array([$service, $actualMethod], [$validated, ...Arr::wrap($args)]);
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

        foreach (['workflow_id', 'document_id', 'mode', 'document_action_id'] as $key) {
            if (empty($data[$key])) {
                throw new \InvalidArgumentException("Missing required field: {$key}");
            }
        }

        $this->document = Document::find($data['document_id']);
        $this->workflow = Workflow::find($data['workflow_id']);

        if (!$this->document || !$this->workflow) {
            throw new \RuntimeException("Invalid document or workflow ID.");
        }
    }

    public function all(): array
    {
        return $this->classMap;
    }

    public function trigger(mixed $service, Document $document, array $args = []): void
    {
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
            $args['amount'] ?? null
        );

        $engine->process();
    }
}
