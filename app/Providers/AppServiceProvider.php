<?php

namespace App\Providers;

use App\Core\Processor;
use App\Models\Document;
use App\Models\DocumentDraft;
use App\Models\Payment;
use App\Models\Setting;
use App\Observers\DocumentDraftObserver;
use App\Observers\DocumentObserver;
use App\Observers\PaymentObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\RecipientResolverServiceInterface::class,
            \App\Services\RecipientResolverService::class
        );

        // Register notification services
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\NotificationTemplateService::class);
        $this->app->singleton(\App\Services\RecipientResolverService::class);

        // Register processor interfaces and implementations
        $this->app->singleton(\App\Core\Contracts\ServiceResolverInterface::class, \App\Core\ServiceResolver::class);
        $this->app->singleton(\App\Core\Contracts\ResourceResolverInterface::class, \App\Core\ResourceResolver::class);
        $this->app->singleton(\App\Core\Contracts\WorkflowProcessorInterface::class, \App\Core\WorkflowProcessor::class);

        // Register main processor with dependencies
        $this->app->singleton('processor', function ($app) {
            return new \App\Core\Processor(
                $app->make(\App\Core\Contracts\ServiceResolverInterface::class),
                $app->make(\App\Core\Contracts\ResourceResolverInterface::class),
                $app->make(\App\Core\Contracts\WorkflowProcessorInterface::class)
            );
        });

        $this->app->alias('processor', \App\Core\Processor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observe Document Draft Model
        DocumentDraft::observe(DocumentDraftObserver::class);
        
        // Observe Document Model for stage advancement (ProgressTracker)
        Document::observe(DocumentObserver::class);
        
        // Observe Payment Model for ProcessCard automation
        Payment::observe(PaymentObserver::class);

        // Register ProcessCard automation events
        Event::listen(
            \App\Events\PaymentCreated::class,
            \App\Listeners\ExecuteProcessCardOnPaymentCreated::class
        );
        
        Event::listen(
            \App\Events\PaymentApproved::class,
            \App\Listeners\ExecuteProcessCardOnPaymentApproved::class
        );
        
        // Register stage advancement event for ProgressTracker-based execution
        Event::listen(
            \App\Events\DocumentStageAdvanced::class,
            \App\Listeners\ExecuteProcessCardOnStageAdvancement::class
        );

        // Event listeners removed - using direct job dispatch instead of events

        RateLimiter::for('document-notify', function () {
            return Limit::perMinute(60); // tune as needed
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        if (Schema::hasTable('settings')) {
            config(['site' => Setting::all(['key', 'value'])->keyBy('key')->transform(function ($setting) {
                    return $setting->value;
                })->toArray()
            ]);
        }
    }
}
