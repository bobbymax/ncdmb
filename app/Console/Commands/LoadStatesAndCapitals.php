<?php

namespace App\Console\Commands;

use App\Helpers\PackContainer;
use App\Services\CityService;
use App\Services\StateService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LoadStatesAndCapitals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:states';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $stateService;
    protected $cityService;
    public function __construct(StateService $stateService, CityService $cityService) {
        parent::__construct();
        $this->stateService = $stateService;
        $this->cityService = $cityService;
    }

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        foreach (PackContainer::states() as $state) {
            $this->cityService->store([
                'name' => $state,
                'has_airport' => true
            ]);
        }
    }
}
