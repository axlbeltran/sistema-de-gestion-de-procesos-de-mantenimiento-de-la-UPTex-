
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis trabajos | UPTEX</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f6f5;
            color: #24332c;
            font-family: Arial, sans-serif;
        }

        header {
            background: #105c40;
            color: white;
            padding: 22px 30px;
        }

        header h2 { margin: 0; }

        main {
            max-width: 1150px;
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

        .cabecera h1 { margin: 0 0 8px; }

        .subtitulo { color: #64748b; }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,.05);
        }

        .tabla { overflow-x: auto; }

        table {
            width: 100%;
            min-width: 780px;
            border-collapse: collapse;
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
            border: none;
            border-radius: 8px;
            padding: 11px 16px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-gris { background: #64748b; }

        .acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .estado {
            display: inline-block;
            padding: 7px 12px;
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

        .alerta {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .exito {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .paginacion { margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Mantenimiento</h2>
</header>

<main>
    <div class="cabecera">
        <div>
            <h1>Mis trabajos asignados</h1>
            <p class="subtitulo">
                Técnico:
                {{ auth()->user()->nombre }}
                {{ auth()->user()->apellido }}
            </p>
        </div>

        <div class="acciones">
            <a href="{{ route('finalizacion.historial') }}"
               class="btn">
                Historial
            </a>

            <a href="{{ route('dashboard') }}"
               class="btn btn-gris">
                Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alerta exito">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alerta error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="panel">
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Ubicación</th>
                        <th>Problema</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($trabajos as $trabajo)
                        <tr>
                            <td>
                                <strong>#{{ $trabajo->id }}</strong>
                            </td>

                            <td>
                                {{ $trabajo->ubicacion }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Str::limit(
                                    $trabajo->problema_descripcion,
                                    110
                                ) }}
                            </td>

                            <td>
                                @php
                                    $clase = match ($trabajo->estado) {
                                        'En Proceso' => 'proceso',
                                        'Terminado' => 'terminado',
                                        default => 'pendiente',
                                    };
                                @endphp

                                <span class="estado {{ $clase }}">
                                    {{ $trabajo->estado }}
                                </span>
                            </td>

                            <td>
                                <div class="acciones">
                                    <a
                                        href="{{ route(
                                            'evidencias.show',
                                            $trabajo->id
                                        ) }}"
                                        class="btn btn-gris"
                                    >
                                        Evidencias
                                    </a>

                                    @if ($trabajo->estado === 'En Proceso')
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'finalizacion.finalizar',
                                                $trabajo->id
                                            ) }}"
                                            onsubmit="return confirm('¿Confirmas que el mantenimiento está terminado? Esta acción no se puede deshacer desde este panel.');"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="btn"
                                            >
                                                Finalizar mantenimiento
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                Todavía no tienes trabajos asignados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            {{ $trabajos->links() }}
        </div>
    </div>
</main>

</body>
</html>
