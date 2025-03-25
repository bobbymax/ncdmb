<?php

namespace App\Providers;

use App\Models\DocumentDraft;
use App\Models\Setting;
use App\Observers\DocumentDraftObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observe Document Draft Model
        DocumentDraft::observe(DocumentDraftObserver::class);

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
