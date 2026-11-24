<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SolicitudMantenimientoController;
use App\Http\Controllers\RegistroSolicitudController;
use App\Http\Controllers\GestionMantenimientoController;
use App\Http\Controllers\EvidenciaMantenimientoController;
use App\Http\Controllers\FinalizacionMantenimientoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\AdministracionUsuarioController;
use App\Http\Controllers\ControlLockersTarjetonesController;
use App\Http\Controllers\ReporteMantenimientoController;
use App\Http\Controllers\AutorizacionMantenimientoController;
use App\Http\Controllers\LevantamientoTecnicoController;

use App\Http\Middleware\VerificarRol;

/*
|--------------------------------------------------------------------------
| RUTAS PUBLICAS
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {

    Route::get('/login', [
        LoginController::class,
        'mostrarLogin'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'iniciarSesion'
    ])->name('login.procesar');

});

/*
|--------------------------------------------------------------------------
| RUTAS AUTENTICADAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD Y SESION
    |--------------------------------------------------------------------------
    */

    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::post('/logout', [
        LoginController::class,
        'cerrarSesion'
    ])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | SOLICITUDES DE MANTENIMIENTO
    |--------------------------------------------------------------------------
    */

    Route::get('/mis-solicitudes', [
        SolicitudMantenimientoController::class,
        'index'
    ])->name('mis-solicitudes');

    Route::prefix('solicitudes')->group(function () {

        Route::get('/nueva', [
            RegistroSolicitudController::class,
            'create'
        ])->name('solicitudes.create');

        Route::post('/', [
            RegistroSolicitudController::class,
            'store'
        ])->name('solicitudes.store');

        Route::get('/{solicitud}/editar', [
            SolicitudMantenimientoController::class,
            'edit'
        ])
            ->whereNumber('solicitud')
            ->name('solicitudes.edit');

        Route::put('/{solicitud}', [
            SolicitudMantenimientoController::class,
            'update'
        ])
            ->whereNumber('solicitud')
            ->name('solicitudes.update');

    });

    /*
    |--------------------------------------------------------------------------
    | REGISTRO DE MANTENIMIENTO
    |--------------------------------------------------------------------------
    */

    Route::prefix('registro-mantenimiento')
        ->name('registro-mantenimiento.')
        ->group(function () {

            Route::get('/', [
                RegistroSolicitudController::class,
                'create'
            ])->name('create');

            Route::post('/', [
                RegistroSolicitudController::class,
                'store'
            ])->name('store');

        });

    /*
    |--------------------------------------------------------------------------
    | GESTION DE MANTENIMIENTO
    |--------------------------------------------------------------------------
    */

    Route::prefix('gestion-mantenimiento')
        ->name('gestion-mantenimiento.')
        ->group(function () {

            Route::get('/', [
                GestionMantenimientoController::class,
                'index'
            ])->name('index');

            Route::middleware(
                VerificarRol::class .
                ':Super Admin,Responsable de Gestion,Responsable de Area'
            )->group(function () {

                Route::get('/{id}/editar', [
                    GestionMantenimientoController::class,
                    'edit'
                ])
                    ->whereNumber('id')
                    ->name('edit');

                Route::put('/{id}', [
                    GestionMantenimientoController::class,
                    'update'
                ])
                    ->whereNumber('id')
                    ->name('update');

            });

        });

    /*
    |--------------------------------------------------------------------------
    | EVIDENCIAS FOTOGRAFICAS
    |--------------------------------------------------------------------------
    */

    Route::prefix('evidencias')
        ->name('evidencias.')
        ->group(function () {

            Route::get('/', [
                EvidenciaMantenimientoController::class,
                'index'
            ])->name('index');

            Route::get('/solicitud/{id}', [
                EvidenciaMantenimientoController::class,
                'show'
            ])
                ->whereNumber('id')
                ->name('show');

            Route::post('/solicitud/{id}', [
                EvidenciaMantenimientoController::class,
                'store'
            ])
                ->whereNumber('id')
                ->name('store');

            Route::get('/imagen/{id}', [
                EvidenciaMantenimientoController::class,
                'imagen'
            ])
                ->whereNumber('id')
                ->name('imagen');

        });

    /*
    |--------------------------------------------------------------------------
    | TRABAJOS DEL TECNICO
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class . ':Tecnico'
    )->group(function () {

        Route::prefix('mis-trabajos-tecnico')
            ->name('finalizacion.')
            ->group(function () {

                Route::get('/', [
                    FinalizacionMantenimientoController::class,
                    'trabajos'
                ])->name('trabajos');

                Route::patch('/{id}/finalizar', [
                    FinalizacionMantenimientoController::class,
                    'finalizar'
                ])
                    ->whereNumber('id')
                    ->name('finalizar');

            });

        Route::redirect(
            '/trabajos',
            '/mis-trabajos-tecnico'
        )->name('trabajos');

    });

    /*
    |--------------------------------------------------------------------------
    | HISTORIAL DE MANTENIMIENTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/historial-mantenimientos', [
        FinalizacionMantenimientoController::class,
        'historial'
    ])->name('finalizacion.historial');

    /*
    |--------------------------------------------------------------------------
    | NOTIFICACIONES
    |--------------------------------------------------------------------------
    */

    Route::prefix('notificaciones')
        ->name('notificaciones.')
        ->group(function () {

            Route::get('/', [
                NotificacionController::class,
                'index'
            ])->name('index');

            Route::patch('/marcar-todas', [
                NotificacionController::class,
                'marcarTodas'
            ])->name('todas');

            Route::patch('/{id}/marcar-leida', [
                NotificacionController::class,
                'marcarLeida'
            ])
                ->whereNumber('id')
                ->name('leida');

        });

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRACION DEL SISTEMA
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class . ':Super Admin'
    )->group(function () {

        Route::redirect(
            '/administracion',
            '/administracion/usuarios'
        )->name('administracion');

        /*
        |--------------------------------------------------------------------------
        | ADMINISTRACION DE USUARIOS
        |--------------------------------------------------------------------------
        */

        Route::prefix('administracion/usuarios')
            ->name('administracion.usuarios.')
            ->group(function () {

                Route::get('/', [
                    AdministracionUsuarioController::class,
                    'index'
                ])->name('index');

                Route::get('/crear', [
                    AdministracionUsuarioController::class,
                    'create'
                ])->name('create');

                Route::post('/', [
                    AdministracionUsuarioController::class,
                    'store'
                ])->name('store');

                Route::get('/{id}/editar', [
                    AdministracionUsuarioController::class,
                    'edit'
                ])
                    ->whereNumber('id')
                    ->name('edit');

                Route::put('/{id}', [
                    AdministracionUsuarioController::class,
                    'update'
                ])
                    ->whereNumber('id')
                    ->name('update');

            });

    });

    /*
    |--------------------------------------------------------------------------
    | CONTROL DE LOCKERS Y TARJETONES
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class .
        ':Super Admin,Responsable de Gestion'
    )->group(function () {

        Route::prefix('control-recursos')
            ->name('control-recursos.')
            ->group(function () {

                Route::get('/', [
                    ControlLockersTarjetonesController::class,
                    'index'
                ])->name('index');

                Route::get('/lockers/crear', [
                    ControlLockersTarjetonesController::class,
                    'crearLocker'
                ])->name('lockers.create');

                Route::post('/lockers', [
                    ControlLockersTarjetonesController::class,
                    'guardarLocker'
                ])->name('lockers.store');

                Route::get('/tarjetones/crear', [
                    ControlLockersTarjetonesController::class,
                    'crearTarjeton'
                ])->name('tarjetones.create');

                Route::post('/tarjetones', [
                    ControlLockersTarjetonesController::class,
                    'guardarTarjeton'
                ])->name('tarjetones.store');

                Route::get('/asignaciones/crear', [
                    ControlLockersTarjetonesController::class,
                    'crearAsignacion'
                ])->name('asignaciones.create');

                Route::post('/asignaciones', [
                    ControlLockersTarjetonesController::class,
                    'guardarAsignacion'
                ])->name('asignaciones.store');

                Route::patch('/asignaciones/{id}/cancelar', [
                    ControlLockersTarjetonesController::class,
                    'cancelarAsignacion'
                ])
                    ->whereNumber('id')
                    ->name('asignaciones.cancelar');

            });

    });

    /*
    |--------------------------------------------------------------------------
    | REPORTES Y ESTADISTICAS
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class .
        ':Super Admin,Responsable de Gestion'
    )->group(function () {

        Route::prefix('reportes')
            ->name('reportes.')
            ->group(function () {

                Route::get('/', [
                    ReporteMantenimientoController::class,
                    'index'
                ])->name('index');

            });

    });

    /*
    |--------------------------------------------------------------------------
    | AUTORIZACIONES DIGITALES
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class . ':Super Admin,Responsable de Area'
    )->group(function () {

        Route::prefix('autorizaciones')
            ->name('autorizaciones.')
            ->group(function () {

                Route::get('/', [
                    AutorizacionMantenimientoController::class,
                    'index'
                ])->name('index');

                Route::get('/{id}/revisar', [
                    AutorizacionMantenimientoController::class,
                    'revisar'
                ])->whereNumber('id')->name('revisar');

                Route::post('/{id}/resolver', [
                    AutorizacionMantenimientoController::class,
                    'resolver'
                ])->whereNumber('id')->name('resolver');

            });

    });

    /*
    |--------------------------------------------------------------------------
    | LEVANTAMIENTOS TECNICOS
    |--------------------------------------------------------------------------
    */

    Route::prefix('levantamientos')
        ->name('levantamientos.')
        ->group(function () {

            Route::get('/', [
                LevantamientoTecnicoController::class,
                'index'
            ])->name('index');

            Route::get('/{id}', [
                LevantamientoTecnicoController::class,
                'show'
            ])->whereNumber('id')->name('show');

            Route::post('/{id}/resolver', [
                LevantamientoTecnicoController::class,
                'resolver'
            ])->middleware(VerificarRol::class . ':Super Admin')
                ->whereNumber('id')->name('resolver');

            Route::middleware(
                VerificarRol::class . ':Super Admin,Responsable de Gestion'
            )->group(function () {

                Route::get('/{id}/crear', [
                    LevantamientoTecnicoController::class,
                    'create'
                ])->whereNumber('id')->name('create');

                Route::post('/{id}', [
                    LevantamientoTecnicoController::class,
                    'store'
                ])->whereNumber('id')->name('store');

                Route::get('/{id}/editar', [
                    LevantamientoTecnicoController::class,
                    'edit'
                ])->whereNumber('id')->name('edit');

                Route::put('/{id}', [
                    LevantamientoTecnicoController::class,
                    'update'
                ])->whereNumber('id')->name('update');

                Route::patch('/{id}/enviar', [
                    LevantamientoTecnicoController::class,
                    'enviarRevision'
                ])->whereNumber('id')->name('enviar');

            });

        });

    /*
    |--------------------------------------------------------------------------
    | ACCESOS SEGUN ROL
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        VerificarRol::class .
        ':Super Admin,Responsable de Gestion'
    )->group(function () {

        Route::redirect(
            '/gestion',
            '/gestion-mantenimiento'
        )->name('gestion');

    });

    Route::middleware(
        VerificarRol::class .
        ':Super Admin,Responsable de Gestion,Responsable de Area'
    )->group(function () {

        Route::redirect(
            '/solicitudes',
            '/mis-solicitudes'
        )->name('solicitudes');

    });

});
