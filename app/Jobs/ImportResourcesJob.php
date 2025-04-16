<?php

namespace App\Jobs;

use App\Interfaces\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Importable $resourceImport;
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(Importable $resourceImport, array $data)
    {
        $this->resourceImport = $resourceImport;
        $this->data = $data;
    }

    /**
     * Execute the job.
     * @throws \ReflectionException
     */
    public function handle(): void
    {
        $reflection = new \ReflectionMethod($this->resourceImport, 'import');
        if (!$reflection->isPublic()) {
            Log::error("Import method exists but is not public.");
            return;
        }

        if (empty($this->data)) {
            Log::error("Import data is empty.");
            return;
        }

        $this->resourceImport->import($this->data);
        Log::info("Successfully imported data via " . get_class($this->resourceImport));
    }
}
