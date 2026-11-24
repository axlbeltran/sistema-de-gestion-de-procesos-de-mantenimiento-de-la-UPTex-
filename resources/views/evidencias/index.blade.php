
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajos y evidencias | UPTEX</title>

    <style>
        * { box-sizing: border-box; }

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

        header h2 { margin: 0; }

        main {
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        h1 { margin: 0 0 8px; }

        .subtitulo {
            color: #64748b;
            margin: 0;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,.05);
        }

        .tabla {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f0f7f3;
            color: #105c40;
        }

        .btn {
            display: inline-block;
            background: #105c40;
            color: white;
            padding: 11px 17px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-gris { background: #64748b; }

        .estado {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .proceso {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .terminado {
            background: #dcfce7;
            color: #166534;
        }

        .vacio {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        .paginacion {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Sistema de Mantenimiento</h2>
</header>

<main>

    <div class="cabecera">
        <div>
            <h1>Trabajos y evidencias</h1>

            <p class="subtitulo">
                Usuario: {{ auth()->user()->nombre }}
                | Rol: {{ $rol }}
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-gris">
            Volver al dashboard
        </a>
    </div>

    <div class="panel">
        <div class="tabla">

            <table>
                <thead>
                    <tr>
                        <th>Solicitud</th>
                        <th>Ubicación</th>
                        <th>Problema</th>
                        <th>Técnico</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($solicitudes as $solicitud)
                        <tr>
                            <td>
                                <strong>#{{ $solicitud->id }}</strong>
                            </td>

                            <td>
                                {{ $solicitud->ubicacion }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Str::limit(
                                    $solicitud->problema_descripcion,
                                    100
                                ) }}
                            </td>

                            <td>
                                @if ($solicitud->tecnico_asignado_id)
                                    {{ $solicitud->tecnico_nombre }}
                                    {{ $solicitud->tecnico_apellido }}
                                @else
                                    Sin asignar
                                @endif
                            </td>

                            <td>
                                @php
                                    $clase = match ($solicitud->estado) {
                                        'Pendiente' => 'pendiente',
                                        'En Proceso' => 'proceso',
                                        'Terminado' => 'terminado',
                                        default => 'pendiente'
                                    };
                                @endphp

                                <span class="estado {{ $clase }}">
                                    {{ $solicitud->estado }}
                                </span>
                            </td>

                            <td>
                                <a
                                    class="btn"
                                    href="{{ route(
                                        'evidencias.show',
                                        $solicitud->id
                                    ) }}"
                                >
                                    Ver evidencias
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="vacio">
                                No hay trabajos disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="paginacion">
            {{ $solicitudes->links() }}
        </div>
    </div>

</main>

</body>
</html>
