<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Template;
use App\Services\Module\ModuleLoader;
use App\Services\Payment\PaymentGatewayRepository;
use App\Services\Payment\StripePaymentGateway;
use App\Services\SeoService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayRepository::class, function () {
            return new PaymentGatewayRepository;
        });

        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return $app->make(StripePaymentGateway::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.admin', function ($view) {
            try {
                $count = Order::where('delivery_status', 'pending')->count();
            } catch (\Throwable $e) {
                $count = 0;
            }
            $view->with('adminPendingOrdersCount', $count);
        });

        View::composer('layouts.app', function ($view) {
            $designerTemplateRevenue = 0;
            if (auth()->check() && auth()->user()->hasRole('designer')) {
                try {
                    $templateIds = Template::where('created_by', auth()->id())->pluck('id');
                    $designerTemplateRevenue = (float) Order::whereIn('template_id', $templateIds)->sum('total_amount');
                } catch (\Throwable $e) {
                    $designerTemplateRevenue = 0;
                }
            }
            $view->with('designerTemplateRevenue', $designerTemplateRevenue);
            try {
                $view->with('seo', app(SeoService::class)->forRequest(request()));
            } catch (\Throwable $e) {
                $view->with('seo', []);
            }
        });

        try {
            app(ModuleLoader::class)->bootstrap();
        } catch (\Throwable $e) {
            // Avoid breaking the app if modules table doesn't exist (e.g. during migrations)
            if (! str_contains($e->getMessage(), "doesn't exist")) {
                report($e);
            }
        }
    }
}
