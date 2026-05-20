<?php

namespace App\Providers;

use App\Repositories\AnalyticsRepository;
use App\Repositories\Contracts\AnalyticsRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AnalyticsRepositoryInterface::class, AnalyticsRepository::class);
    }
}
