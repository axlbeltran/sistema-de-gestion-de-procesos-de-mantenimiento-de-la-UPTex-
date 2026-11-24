<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de mantenimiento | UPTEX</title>

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
            padding: 20px 30px;
        }

        header h2 { margin: 0; }

        main {
            max-width: 1250px;
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

        .btn {
            display: inline-block;
            background: #105c40;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 11px 17px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-gris { background: #64748b; }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
            margin-bottom: 22px;
        }

        .filtros {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        input, select {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        input { flex: 1; min-width: 200px; }

        .tabla-contenedor { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        th {
            background: #f0f7f3;
            color: #105c40;
            font-size: 13px;
        }

        td { font-size: 14px; }

        .estado {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
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

        .alerta {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .vacio {
            text-align: center;
            color: #64748b;
            padding: 30px;
        }

        .paginacion { margin-top: 20px; }

        .paginacion nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Gestión de Mantenimiento</h2>
</header>

<main>

    <div class="cabecera">
        <div>
            <h1>Control de mantenimiento</h1>
            <p class="subtitulo">
                Sesión: {{ auth()->user()->nombre }}
                | Rol: {{ $rol }}
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

        <form method="GET"
              action="{{ route('gestion-mantenimiento.index') }}"
              class="filtros">

            <input
                type="text"
                name="buscar"
                placeholder="Buscar por folio, ubicación, problema o solicitante"
                value="{{ request('buscar') }}"
            >

            <select name="estado">
                <option value="">Todos los estados</option>

                @foreach (['Pendiente', 'En Proceso', 'Terminado'] as $estado)
                    <option
                        value="{{ $estado }}"
                        @selected(request('estado') === $estado)
                    >
                        {{ $estado }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn">
                Buscar
            </button>

            <a href="{{ route('gestion-mantenimiento.index') }}"
               class="btn btn-gris">
                Limpiar
            </a>
        </form>

    </div>

    <div class="panel">

        <div class="tabla-contenedor">

            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Ubicación</th>
                        <th>Problema</th>
                        <th>Urgencia</th>
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
                                {{ $solicitud->solicitante_nombre }}
                                {{ $solicitud->solicitante_apellido }}
                            </td>

                            <td>
                                {{ $solicitud->ubicacion }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Str::limit(
                                    $solicitud->problema_descripcion,
                                    80
                                ) }}
                            </td>

                            <td>
                                {{ $solicitud->nivel_urgencia }}
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
                                    $claseEstado = match ($solicitud->estado) {
                                        'Pendiente' => 'pendiente',
                                        'En Proceso' => 'proceso',
                                        'Terminado' => 'terminado',
                                        default => 'pendiente'
                                    };
                                @endphp

                                <span class="estado {{ $claseEstado }}">
                                    {{ $solicitud->estado }}
                                </span>
                            </td>

                            <td>
                                @if ($puedeAdministrar)
                                    <a
                                        class="btn"
                                        href="{{ route(
                                            'gestion-mantenimiento.edit',
                                            $solicitud->id
                                        ) }}"
                                    >
                                        Gestionar
                                    </a>
                                @else
                                    Sin acciones disponibles
                                @endif
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="vacio">
                                No se encontraron solicitudes.
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
