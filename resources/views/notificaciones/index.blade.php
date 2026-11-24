
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones | UPTEX</title>

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
            padding: 22px 30px;
        }

        header h2 {
            margin: 0;
        }

        main {
            max-width: 1050px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0 0 8px;
        }

        .subtitulo {
            color: #64748b;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.05);
        }

        .resumen {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .contador {
            background: #dcfce7;
            color: #166534;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
        }

        .notificacion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .no-leida {
            background: #f0fdf4;
            border-left: 5px solid #15803d;
        }

        .leida {
            background: #fafafa;
        }

        .tipo {
            display: inline-block;
            font-size: 12px;
            font-weight: bold;
            background: #e2e8f0;
            color: #334155;
            padding: 6px 10px;
            border-radius: 20px;
            margin-bottom: 8px;
        }

        .mensaje {
            margin: 8px 0;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .fecha {
            color: #64748b;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            background: #105c40;
            color: white;
            padding: 11px 16px;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-gris {
            background: #64748b;
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .alerta {
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .vacio {
            text-align: center;
            padding: 50px 15px;
            color: #64748b;
        }

        .paginacion {
            margin-top: 20px;
        }

        @media (max-width: 650px) {
            .notificacion {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Centro de Notificaciones</h2>
</header>

<main>

    <div class="cabecera">
        <div>
            <h1>🔔 Mis notificaciones</h1>
            <p class="subtitulo">
                Consulta las novedades de tus solicitudes
                de mantenimiento.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-gris">
            Volver al dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="alerta">
            {{ session('success') }}
        </div>
    @endif

    <div class="panel">

        <div class="resumen">
            <div class="contador">
                {{ $sinLeer }}
                {{ $sinLeer === 1 ? 'notificacion sin leer' : 'notificaciones sin leer' }}
            </div>

            <form
                action="{{ route('notificaciones.todas') }}"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="btn"
                    {{ $sinLeer === 0 ? 'disabled' : '' }}
                >
                    Marcar todas como leidas
                </button>
            </form>
        </div>

        @forelse ($notificaciones as $notificacion)

            <div class="notificacion {{ $notificacion->leido ? 'leida' : 'no-leida' }}">

                <div>
                    <span class="tipo">
                        @switch($notificacion->tipo)
                            @case('Asignacion')
                                👷 Asignacion
                                @break

                            @case('Vencimiento')
                                ⏰ Vencimiento
                                @break

                            @default
                                🔧 Mantenimiento
                        @endswitch
                    </span>

                    <p class="mensaje">
                        {{ $notificacion->mensaje }}
                    </p>

                    <span class="fecha">
                        {{ $notificacion->fecha_creacion }}
                    </span>

                    @if (!$notificacion->leido)
                        <p>
                            <strong>Sin leer</strong>
                        </p>
                    @endif
                </div>

                @if (!$notificacion->leido)
                    <form
                        action="{{ route('notificaciones.leida', $notificacion->id) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn">
                            Marcar como leida
                        </button>
                    </form>
                @endif

            </div>

        @empty

            <div class="vacio">
                <h2>No tienes notificaciones</h2>
                <p>
                    Cuando recibas un aviso, aparecera aqui.
                </p>
            </div>

        @endforelse

        <div class="paginacion">
            {{ $notificaciones->links() }}
        </div>

    </div>

</main>

</body>
</html>
