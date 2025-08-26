<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TelegramService;
use App\Services\Telegram\TelegramAuthService;
use App\Services\Telegram\TelegramConnectionService;
use App\Services\Telegram\TelegramDialogService;
use App\Services\Telegram\TelegramMessageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramConnectionService::class);
        $this->app->singleton(TelegramAuthService::class);
        $this->app->singleton(TelegramDialogService::class);
        $this->app->singleton(TelegramMessageService::class);
        $this->app->singleton(TelegramService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
