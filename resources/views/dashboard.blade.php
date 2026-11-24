<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal | UPTEX</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6f5;
            color: #24332c;
        }

        header {
            background: #105c40;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        header h2 {
            margin: 0;
            font-size: 21px;
        }

        .header-acciones {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .campanita {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: background .2s;
        }

        .campanita:hover {
            background: rgba(255,255,255,.15);
        }

        .campanita svg {
            width: 26px;
            height: 26px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .contador {
            position: absolute;
            top: -3px;
            right: -5px;
            min-width: 21px;
            height: 21px;
            padding: 0 5px;
            background: #ef4444;
            color: white;
            border: 2px solid #105c40;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }

        .cerrar-sesion {
            border: 0;
            border-radius: 8px;
            padding: 12px 18px;
            background: white;
            color: #105c40;
            font-weight: bold;
            cursor: pointer;
        }

        .cerrar-sesion:hover {
            background: #e5f5eb;
        }

        main {
            max-width: 1150px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .bienvenida {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 18px rgba(0,0,0,.04);
        }

        .bienvenida h1 {
            margin-top: 0;
        }

        .rol {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            border-radius: 20px;
            padding: 9px 15px;
            font-weight: bold;
        }

        .tarjetas {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .tarjeta {
            display: block;
            background: white;
            border-radius: 13px;
            padding: 25px;
            border-left: 5px solid #16805b;
            color: inherit;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 18px rgba(0,0,0,.04);
        }

        .tarjeta:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,.09);
        }

        .tarjeta h3 {
            color: #105c40;
            margin-top: 0;
        }

        .tarjeta p {
            line-height: 1.6;
            color: #64748b;
        }

        .tarjeta-icono {
            font-size: 28px;
            margin-bottom: 12px;
        }

        .aviso {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        @media (max-width: 650px) {
            header {
                flex-direction: column;
                text-align: center;
                padding: 20px 15px;
            }

            .header-acciones {
                justify-content: center;
            }

            .bienvenida {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

@php
    $usuario = auth()->user();

    $rolId = (int) $usuario->rol_id;

    $notificacionesSinLeer = \Illuminate\Support\Facades\DB::table(
        'notificaciones'
    )
        ->where('usuario_id', $usuario->id)
        ->where(function ($consulta) {
            $consulta->where('leido', 0)
                ->orWhereNull('leido');
        })
        ->count();
@endphp

<header>

    <h2>UPTEX | Gestión de Mantenimiento</h2>

    <div class="header-acciones">

        <a
            href="{{ route('notificaciones.index') }}"
            class="campanita"
            title="Ver notificaciones"
            aria-label="Notificaciones: {{ $notificacionesSinLeer }} sin leer"
        >

            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
            >
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                <path d="M10 21h4"/>
            </svg>

            @if ($notificacionesSinLeer > 0)
                <span class="contador">
                    {{ $notificacionesSinLeer > 99
                        ? '99+'
                        : $notificacionesSinLeer }}
                </span>
            @endif

        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="cerrar-sesion">
                Cerrar sesión
            </button>
        </form>

    </div>

</header>

<main>

    <section class="bienvenida">

        <h1>
            Bienvenido,
            {{ $usuario->nombre }}
            {{ $usuario->apellido }}
        </h1>

        <p>
            Sistema de Gestión de Procesos de Mantenimiento
        </p>

        <span class="rol">
            {{ $usuario->rol?->nombre_rol ?? 'Sin rol' }}
        </span>

    </section>

    <section class="tarjetas">

        @if ($rolId === 1)
            <a
                class="tarjeta"
                href="{{ route('administracion') }}"
            >
                <div class="tarjeta-icono">⚙️</div>

                <h3>Administración</h3>

                <p>
                    Administración de usuarios
                    y configuración del sistema.
                </p>
            </a>
        @endif

        @if (in_array($rolId, [1, 2], true))
            <a
                class="tarjeta"
                href="{{ route('gestion') }}"
            >
                <div class="tarjeta-icono">📋</div>

                <h3>Gestión general</h3>

                <p>
                    Asignar técnicos, administrar
                    solicitudes y actualizar estados.
                </p>
            </a>
        @endif

        @if (in_array($rolId, [1, 2, 3], true))
            <a
                class="tarjeta"
                href="{{ route('solicitudes') }}"
            >
                <div class="tarjeta-icono">📝</div>

                <h3>Solicitudes</h3>

                <p>
                    Consultar las solicitudes
                    de mantenimiento registradas.
                </p>
            </a>
        @endif

        @if ($rolId === 4)
            <a
                class="tarjeta"
                href="{{ route('finalizacion.trabajos') }}"
            >
                <div class="tarjeta-icono">🔧</div>

                <h3>Mis trabajos</h3>

                <p>
                    Consultar trabajos asignados,
                    registrar evidencias y finalizar
                    mantenimientos.
                </p>
            </a>
        @endif

        @if (in_array($rolId, [1, 2, 3], true))
            <a
                class="tarjeta"
                href="{{ route('gestion-mantenimiento.index') }}"
            >
                <div class="tarjeta-icono">🏢</div>

                <h3>Control de mantenimiento</h3>

                <p>
                    Revisar el estado y seguimiento
                    de las solicitudes.
                </p>
            </a>
        @endif

        <a
            class="tarjeta"
            href="{{ route('mis-solicitudes') }}"
        >
            <div class="tarjeta-icono">📂</div>

            <h3>Mis solicitudes</h3>

            <p>
                Consultar las solicitudes
                relacionadas con tu cuenta.
            </p>
        </a>

        <a
            class="tarjeta"
            href="{{ route('solicitudes.create') }}"
        >
            <div class="tarjeta-icono">➕</div>

            <h3>Nueva solicitud</h3>

            <p>
                Reportar una falla o necesidad
                de mantenimiento.
            </p>
        </a>

        <a
            class="tarjeta"
            href="{{ route('finalizacion.historial') }}"
        >
            <div class="tarjeta-icono">📚</div>

            <h3>Historial</h3>

            <p>
                Consultar los mantenimientos
                que ya fueron terminados.
            </p>
        </a>

        <a
            class="tarjeta"
            href="{{ route('notificaciones.index') }}"
        >
            <div class="tarjeta-icono">🔔</div>

            <h3>Notificaciones</h3>

            <p>
                Consultar avisos sobre asignaciones,
                cambios de estado y mantenimiento.
            </p>

            @if ($notificacionesSinLeer > 0)
                <span class="aviso">
                    {{ $notificacionesSinLeer }}
                    sin leer
                </span>
            @endif

        </a>

        @if (in_array($rolId, [1, 2], true))
            <a
                class="tarjeta"
                href="{{ route('control-recursos.index') }}"
            >
                <div class="tarjeta-icono">🔐</div>

                <h3>Lockers y tarjetones</h3>

                <p>
                    Administrar recursos, asignaciones
                    y consultar vencimientos.
                </p>
            </a>

            <a
                class="tarjeta"
                href="{{ route('reportes.index') }}"
            >
                <div class="tarjeta-icono">📊</div>

                <h3>Reportes y estadísticas</h3>

                <p>
                    Consultar indicadores, gráficas
                    y seguimiento de mantenimientos.
                </p>
            </a>
        @endif

        @if (in_array($rolId, [1, 3], true))
            <a
                class="tarjeta"
                href="{{ route('autorizaciones.index') }}"
            >
                <div class="tarjeta-icono">📋</div>

                <h3>Autorizaciones digitales</h3>

                <p>
                    Revisar, aprobar o rechazar
                    solicitudes de mantenimiento.
                </p>
            </a>
        @endif

        <a
            class="tarjeta"
            href="{{ route('levantamientos.index') }}"
        >
            <div class="tarjeta-icono">🛠️</div>

            <h3>Levantamientos técnicos</h3>

            <p>
                {{ in_array($rolId, [1, 2], true)
                    ? 'Registrar y gestionar diagnósticos técnicos.'
                    : 'Consultar los diagnósticos relacionados con tu cuenta.' }}
            </p>
        </a>

    </section>

</main>

</body>
</html>
