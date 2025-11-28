<?php

namespace App\Providers;

use App\Repositories\Contracts\AsociadoRepositoryInterface;
use App\Repositories\Contracts\MovimientoRepositoryInterface;
use App\Repositories\Contracts\OrganizacionRepositoryInterface;
use App\Repositories\Contracts\ProveedorRepositoryInterface;
use App\Repositories\Contracts\ProyectoRepositoryInterface;
use App\Repositories\AsociadoRepository;
use App\Repositories\MovimientoRepository;
use App\Repositories\OrganizacionRepository;
use App\Repositories\ProveedorRepository;
use App\Repositories\ProyectoRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider para Repositories
 * 
 * Registra los bindings de las interfaces de repositorio
 * con sus implementaciones concretas.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registrar servicios.
     *
     * @return void
     */
    public function register(): void
    {
        // Binding del repositorio de Proveedores
        $this->app->bind(
            ProveedorRepositoryInterface::class,
            ProveedorRepository::class
        );

        // Binding del repositorio de Asociados
        $this->app->bind(
            AsociadoRepositoryInterface::class,
            AsociadoRepository::class
        );

        // Binding del repositorio de Proyectos
        $this->app->bind(
            ProyectoRepositoryInterface::class,
            ProyectoRepository::class
        );

        // Binding del repositorio de Organizaciones
        $this->app->bind(
            OrganizacionRepositoryInterface::class,
            OrganizacionRepository::class
        );

        // Binding del repositorio de Movimientos
        $this->app->bind(
            MovimientoRepositoryInterface::class,
            MovimientoRepository::class
        );
    }

    /**
     * Bootstrap servicios.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
