<?php

namespace App\Providers;

use App\Http\Resources\MeetingResource;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\MeetingRepository;
use App\Services\MeetingService;

class MeetingServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the MeetingRepository to MeetingService
        $this->app->bind(MeetingService::class, function ($app) {
            $meetingRepository = $app->make(MeetingRepository::class);
            $meetingResource = $app->make(MeetingResource::class);
            $uploadRepository = $app->make(UploadRepository::class);
            $reserveRepository = $app->make(ReserveRepository::class);
            $userRepository = $app->make(UserRepository::class);

            return new MeetingService($meetingRepository, $meetingResource, $uploadRepository, $reserveRepository, $userRepository);
        });
    }
}
