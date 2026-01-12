<?php

declare(strict_types=1);

namespace Initred\BoostGuidelines;

use Illuminate\Support\ServiceProvider;
use Initred\BoostGuidelines\Commands\InstallGuidelinesCommand;

class BoostGuidelinesServiceProvider extends ServiceProvider
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
        $this->registerCommands();
        $this->registerPublishing();
    }

    /**
     * Register the Artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallGuidelinesCommand::class,
            ]);
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../.ai/guidelines' => base_path('.ai/guidelines'),
            ], 'boost-guidelines');
        }
    }
}
