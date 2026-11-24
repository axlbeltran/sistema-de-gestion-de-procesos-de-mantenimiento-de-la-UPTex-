
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de mantenimiento | UPTEX</title>
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
            min-width: 900px;
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
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .estado {
            background: #dcfce7;
            color: #166534;
            border-radius: 20px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .paginacion { margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Historial de Mantenimiento</h2>
</header>

<main>
    <div class="cabecera">
        <div>
            <h1>Historial de mantenimientos</h1>
            <p class="subtitulo">
                Solicitudes que ya fueron terminadas
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn">
            Volver al dashboard
        </a>
    </div>

    <div class="panel">
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Ubicación</th>
                        <th>Problema</th>
                        <th>Técnico</th>
                        <th>Última actualización</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($historial as $solicitud)
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
                                    100
                                ) }}
                            </td>

                            <td>
                                @if ($solicitud->tecnico_nombre)
                                    {{ $solicitud->tecnico_nombre }}
                                    {{ $solicitud->tecnico_apellido }}
                                @else
                                    Sin técnico registrado
                                @endif
                            </td>

                            <td>
                                {{ $solicitud->fecha_actualizacion }}
                            </td>

                            <td>
                                <span class="estado">
                                    Terminado
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                No hay mantenimientos terminados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            {{ $historial->links() }}
        </div>
    </div>
</main>

</body>
</html>
