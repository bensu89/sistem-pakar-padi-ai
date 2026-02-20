<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Services\AIServiceInterface::class , function ($app) {
            $provider = config('services.ai.provider', 'groq');

            $gemini = new \App\Services\GeminiService();
            $groq = new \App\Services\GroqService();

            if ($provider === 'gemini') {
                return new \App\Services\FallbackAIService($gemini, $groq, 'gemini', 'groq');
            }

            return new \App\Services\FallbackAIService($groq, $gemini, 'groq', 'gemini');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
