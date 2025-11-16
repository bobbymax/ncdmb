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

    public string $importClass;
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(string $importClass, array $data)
    {
        $this->importClass = $importClass;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $import = app($this->importClass);
        } catch (\Throwable $e) {
            Log::error("Failed to resolve import class {$this->shortName($this->importClass)}: {$e->getMessage()}");
            return;
        }

        if (!($import instanceof Importable)) {
            Log::error("Resolved import class {$this->shortName($this->importClass)} does not implement App\\Interfaces\\Importable.");
            return;
        }

        if (empty($this->data)) {
            Log::error("Import data is empty for {$this->shortName($this->importClass)}.");
            return;
        }

        // Normalize payload shape:
        // - Some imports (e.g., UserImport) expect an array of chunks (nested).
        // - Others (e.g., FundImport) expect a flat array of rows.
        // Strategy: Pass a nested single-chunk payload; imports needing flat arrays should unwrap.
        $payload = [$this->data];

        try {
            $result = $import->import($payload);
            Log::info("Successfully imported via {$this->shortName($this->importClass)}", ['result' => $result]);
        } catch (\Throwable $e) {
            Log::error("Import failed in {$this->shortName($this->importClass)}: {$e->getMessage()}");
        }
    }

    private function shortName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        return end($parts) ?: $fqcn;
    }
}
