<?php

namespace App\Providers;

use App\Domain\Marketplace\Contracts\MarketplaceHttpClient;
use App\Domain\Marketplace\Contracts\ProviderErrorNormalizer;
use App\Domain\Marketplace\Http\LaravelMarketplaceHttpClient;
use App\Domain\Marketplace\Providers\Trendyol\TrendyolProvider;
use App\Domain\Marketplace\Support\MarketplaceProviderErrorNormalizer;
use App\Domain\Marketplace\Support\MarketplaceProviderFactory;
use App\Domain\Marketplace\Support\MarketplaceProviderRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MarketplaceProviderRegistry::class, function ($app): MarketplaceProviderRegistry {
            $registry = MarketplaceProviderRegistry::withDefaultProviders();
            $registry->register($app->make(TrendyolProvider::class));

            return $registry;
        });

        $this->app->singleton(MarketplaceProviderFactory::class, fn ($app) => new MarketplaceProviderFactory(
            $app->make(MarketplaceProviderRegistry::class),
        ));

        $this->app->bind(MarketplaceHttpClient::class, LaravelMarketplaceHttpClient::class);
        $this->app->bind(ProviderErrorNormalizer::class, MarketplaceProviderErrorNormalizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
