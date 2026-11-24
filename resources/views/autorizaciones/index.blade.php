
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorizaciones | UPTEX</title>

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
            max-width: 1250px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 22px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1, h2 {
            margin-top: 0;
        }

        .subtitulo {
            color: #64748b;
            line-height: 1.6;
        }

        .boton {
            display: inline-block;
            background: #16805b;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .gris {
            background: #64748b;
        }

        .buscador {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        input {
            flex: 1;
            min-width: 180px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        .tabla {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #ecfdf5;
            color: #065f46;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .estado {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .aprobada {
            background: #dcfce7;
            color: #166534;
        }

        .rechazada {
            background: #fee2e2;
            color: #991b1b;
        }

        .mensaje {
            background: #dcfce7;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .paginacion {
            margin-top: 20px;
        }

        .paginacion svg {
            width: 20px;
            height: 20px;
        }

        @media(max-width: 600px) {
            main {
                padding: 0 12px;
            }

            .panel {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Autorizaciones digitales</h2>
</header>

<main>

    <div class="panel encabezado">

        <div>
            <h1>Autorizaciones de mantenimiento</h1>

            <p class="subtitulo">
                Revision y autorizacion de solicitudes registradas.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="boton gris">
            Volver al panel
        </a>

    </div>

    @if(session('success'))

        <div class="mensaje">
            {{ session('success') }}
        </div>

    @endif

    <div class="panel">

        <form method="GET"
              action="{{ route('autorizaciones.index') }}"
              class="buscador">

            <input
                type="text"
                name="buscar"
                value="{{ request('buscar') }}"
                placeholder="Buscar folio, solicitante o ubicacion"
            >

            <button type="submit" class="boton">
                Buscar
            </button>

            <a href="{{ route('autorizaciones.index') }}"
               class="boton gris">
                Limpiar
            </a>

        </form>

    </div>

    <div class="panel">

        <h2>Solicitudes para revision</h2>

        <div class="tabla">

            <table>

                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Area</th>
                        <th>Servicio</th>
                        <th>Urgencia</th>
                        <th>Autorizacion</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($solicitudes as $solicitud)

                        <tr>

                            <td>#{{ $solicitud->id }}</td>

                            <td>
                                {{ $solicitud->solicitante_nombre }}
                                {{ $solicitud->solicitante_apellido }}
                            </td>

                            <td>{{ $solicitud->nombre_area }}</td>

                            <td>{{ $solicitud->nombre_servicio }}</td>

                            <td>{{ $solicitud->nivel_urgencia }}</td>

                            <td>

                                @if($solicitud->decision_autorizacion === 'Aprobada')

                                    <span class="estado aprobada">
                                        Aprobada
                                    </span>

                                @elseif($solicitud->decision_autorizacion === 'Rechazada')

                                    <span class="estado rechazada">
                                        Rechazada
                                    </span>

                                @else

                                    <span class="estado pendiente">
                                        Pendiente
                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('autorizaciones.revisar', $solicitud->id) }}"
                                   class="boton">
                                    Revisar
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7">
                                No hay solicitudes disponibles.
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
