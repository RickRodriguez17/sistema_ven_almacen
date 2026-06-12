<?php

namespace App\Providers;

use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $negocio = config('negocio');

            try {
                if (Schema::hasTable('empresas')) {
                    $empresa = Empresa::actual();
                    if ($empresa) {
                        $negocio = array_merge($negocio, $empresa->toNegocio());
                    }
                }
            } catch (\Throwable $e) {
                // BD no disponible (instalación inicial); usar config como fallback
            }

            $view->with('negocio', $negocio);
        });
    }
}
