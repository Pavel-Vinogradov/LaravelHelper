<?php

namespace Palax\LaravelHelpers\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ModularRouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->publishes([
            __DIR__.'/../../config/modular.php' => config_path('modular.php'),
        ]);

    }

    public function boot(): void
    {

        $modules = config('modular.modules');
        $path = config('modular.path');
        if ($modules) {
            Route::group([
                'prefix' => '',
            ], function () use ($modules, $path) {
                foreach ($modules as $mod => $submodules) {
                    foreach ($submodules as $key => $sub) {
                        $relativePath = "/$mod/$sub";

                        Route::prefix('api')
                            ->middleware('api')
                            ->group(function () use ($mod, $sub, $relativePath, $path) {
                                $this->getApiRoutes($mod, $sub, $relativePath, $path);
                            });
                    }
                }
            });
        }
    }

    private function getApiRoutes(string $mod, string $sub, string $relativePath, string $path): void
    {
        $routesPath = $path.$relativePath.'/Routes/api.php';
        if (file_exists($routesPath)) {
            Route::group(
                [
                    'prefix' => strtolower($mod),
                    'middleware' => $this->getMiddleware($mod, 'api'),
                ],
                function () use ($mod, $sub, $routesPath) {
                    Route::namespace("App\Modules\\$mod\\$sub\Controllers")->
                    group($routesPath);
                }
            );
        }
    }

    private function getMiddleware($mod, $key = 'web'): array
    {
        $middleware = [];

        $config = config('modular.groupMiddleware');

        if (isset($config[$mod])) {
            if (array_key_exists($key, $config[$mod])) {
                $middleware = array_merge($middleware, $config[$mod][$key]);
            }
        }

        return $middleware;
    }
}
