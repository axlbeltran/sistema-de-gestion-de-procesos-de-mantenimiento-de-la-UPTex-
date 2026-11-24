
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y estadisticas | UPTEX</title>

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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 24px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
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
        }

        .boton {
            display: inline-block;
            background: #16805b;
            color: white;
            padding: 12px 18px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .gris {
            background: #64748b;
        }

        .filtros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .tarjetas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .tarjeta {
            background: white;
            padding: 24px;
            border-radius: 12px;
            border-left: 6px solid #105c40;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }

        .tarjeta h3 {
            font-size: 15px;
            margin: 0;
            color: #64748b;
        }

        .numero {
            font-size: 38px;
            font-weight: bold;
            margin: 14px 0 0;
        }

        .pendiente {
            border-color: #d97706;
        }

        .proceso {
            border-color: #2563eb;
        }

        .terminado {
            border-color: #16a34a;
        }

        .graficas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .barra-fondo {
            width: 100%;
            height: 18px;
            background: #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
        }

        .barra {
            height: 100%;
            background: #16805b;
            border-radius: 20px;
        }

        .dato-grafica {
            margin-bottom: 20px;
        }

        .dato-titulo {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .distribucion {
            display: flex;
            height: 30px;
            border-radius: 8px;
            overflow: hidden;
            background: #e2e8f0;
            margin: 20px 0;
        }

        .leyenda {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 14px;
        }

        .punto {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .tabla {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
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
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .estado-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .estado-proceso {
            background: #dbeafe;
            color: #1e40af;
        }

        .estado-terminado {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
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

            .graficas {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Reportes y estadisticas</h2>
</header>

<main>

    <div class="panel encabezado">

        <div>
            <h1>Reportes de mantenimiento</h1>
            <p class="subtitulo">
                Seguimiento y analisis de los servicios registrados.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="boton gris">
            Volver al panel
        </a>

    </div>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="panel">

        <h2>Filtros de consulta</h2>

        <form method="GET" action="{{ route('reportes.index') }}">

            <div class="filtros">

                <div>
                    <label>Fecha inicial</label>
                    <input type="date" name="fecha_inicio"
                           value="{{ request('fecha_inicio') }}">
                </div>

                <div>
                    <label>Fecha final</label>
                    <input type="date" name="fecha_fin"
                           value="{{ request('fecha_fin') }}">
                </div>

                <div>
                    <label>Area</label>

                    <select name="area_id">
                        <option value="">Todas las areas</option>

                        @foreach($areas as $area)
                            <option value="{{ $area->id }}"
                                @selected(request('area_id') == $area->id)>
                                {{ $area->nombre_area }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label>Tipo de servicio</label>

                    <select name="tipo_servicio_id">
                        <option value="">Todos los servicios</option>

                        @foreach($tiposServicio as $tipo)
                            <option value="{{ $tipo->id }}"
                                @selected(request('tipo_servicio_id') == $tipo->id)>
                                {{ $tipo->nombre_servicio }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label>Estado</label>

                    <select name="estado">
                        <option value="">Todos los estados</option>

                        <option value="Pendiente"
                            @selected(request('estado') === 'Pendiente')>
                            Pendiente
                        </option>

                        <option value="En Proceso"
                            @selected(request('estado') === 'En Proceso')>
                            En Proceso
                        </option>

                        <option value="Terminado"
                            @selected(request('estado') === 'Terminado')>
                            Terminado
                        </option>
                    </select>
                </div>

            </div>

            <div class="acciones">
                <button class="boton" type="submit">
                    Aplicar filtros
                </button>

                <a href="{{ route('reportes.index') }}" class="boton gris">
                    Limpiar filtros
                </a>
            </div>

        </form>

    </div>

    <div class="tarjetas">

        <div class="tarjeta">
            <h3>Total de solicitudes</h3>
            <div class="numero">{{ $total }}</div>
        </div>

        <div class="tarjeta pendiente">
            <h3>Pendientes</h3>
            <div class="numero">{{ $pendientes }}</div>
        </div>

        <div class="tarjeta proceso">
            <h3>En proceso</h3>
            <div class="numero">{{ $enProceso }}</div>
        </div>

        <div class="tarjeta terminado">
            <h3>Terminadas</h3>
            <div class="numero">{{ $terminadas }}</div>
        </div>

    </div>

    <div class="panel">

        <h2>Porcentaje de finalizacion</h2>

        <div class="numero">
            {{ $porcentajeFinalizacion }}%
        </div>

        <br>

        <div class="barra-fondo">
            <div class="barra"
                 style="width: {{ $porcentajeFinalizacion }}%;">
            </div>
        </div>

        <p class="subtitulo">
            {{ $terminadas }} de {{ $total }} solicitudes terminadas.
        </p>

    </div>

    <div class="panel">

        <h2>Distribucion por estado</h2>

        @if($total > 0)

            <div class="distribucion">

                <div style="background:#d97706;width:{{ ($pendientes / $total) * 100 }}%;"
                     title="Pendientes: {{ $pendientes }}">
                </div>

                <div style="background:#2563eb;width:{{ ($enProceso / $total) * 100 }}%;"
                     title="En proceso: {{ $enProceso }}">
                </div>

                <div style="background:#16a34a;width:{{ ($terminadas / $total) * 100 }}%;"
                     title="Terminadas: {{ $terminadas }}">
                </div>

            </div>

            <div class="leyenda">

                <span>
                    <span class="punto" style="background:#d97706;"></span>
                    Pendientes: {{ $pendientes }}
                </span>

                <span>
                    <span class="punto" style="background:#2563eb;"></span>
                    En proceso: {{ $enProceso }}
                </span>

                <span>
                    <span class="punto" style="background:#16a34a;"></span>
                    Terminadas: {{ $terminadas }}
                </span>

            </div>

        @else

            <p>No hay solicitudes para los filtros seleccionados.</p>

        @endif

    </div>

    <div class="graficas">

        <div class="panel">

            <h2>Solicitudes por servicio</h2>

            @forelse($porServicio as $servicio)

                <div class="dato-grafica">

                    <div class="dato-titulo">
                        <span>{{ $servicio->nombre_servicio ?? 'Sin servicio' }}</span>
                        <strong>{{ $servicio->total }}</strong>
                    </div>

                    <div class="barra-fondo">
                        <div class="barra"
                             style="width:{{ $total > 0 ? ($servicio->total / $total) * 100 : 0 }}%;">
                        </div>
                    </div>

                </div>

            @empty

                <p>No hay informacion disponible.</p>

            @endforelse

        </div>

        <div class="panel">

            <h2>Solicitudes por area</h2>

            @forelse($porArea as $area)

                <div class="dato-grafica">

                    <div class="dato-titulo">
                        <span>{{ $area->nombre_area ?? 'Sin area' }}</span>
                        <strong>{{ $area->total }}</strong>
                    </div>

                    <div class="barra-fondo">
                        <div class="barra"
                             style="width:{{ $total > 0 ? ($area->total / $total) * 100 : 0 }}%;">
                        </div>
                    </div>

                </div>

            @empty

                <p>No hay informacion disponible.</p>

            @endforelse

        </div>

    </div>

    <div class="panel">

        <h2>Seguimiento de tecnicos</h2>

        <div class="tabla">

            <table>

                <thead>
                    <tr>
                        <th>Tecnico</th>
                        <th>Trabajos asignados</th>
                        <th>Trabajos terminados</th>
                        <th>Porcentaje de finalizacion</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($porTecnico as $tecnico)

                        <tr>

                            <td>
                                {{ $tecnico->nombre }}
                                {{ $tecnico->apellido }}
                            </td>

                            <td>{{ $tecnico->total }}</td>

                            <td>{{ $tecnico->terminadas }}</td>

                            <td>
                                {{ $tecnico->total > 0
                                    ? round(($tecnico->terminadas / $tecnico->total) * 100, 2)
                                    : 0 }}%
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4">
                                No hay trabajos asignados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="panel">

        <h2>Detalle de solicitudes</h2>

        <div class="tabla">

            <table>

                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Area</th>
                        <th>Servicio</th>
                        <th>Ubicacion</th>
                        <th>Tecnico</th>
                        <th>Urgencia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
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

                            <td>{{ $solicitud->ubicacion }}</td>

                            <td>
                                @if($solicitud->tecnico_nombre)
                                    {{ $solicitud->tecnico_nombre }}
                                    {{ $solicitud->tecnico_apellido }}
                                @else
                                    Sin asignar
                                @endif
                            </td>

                            <td>{{ $solicitud->nivel_urgencia }}</td>

                            <td>

                                @if($solicitud->estado === 'Pendiente')

                                    <span class="estado estado-pendiente">
                                        Pendiente
                                    </span>

                                @elseif($solicitud->estado === 'En Proceso')

                                    <span class="estado estado-proceso">
                                        En Proceso
                                    </span>

                                @elseif($solicitud->estado === 'Terminado')

                                    <span class="estado estado-terminado">
                                        Terminado
                                    </span>

                                @else

                                    {{ $solicitud->estado ?? 'Sin estado' }}

                                @endif

                            </td>

                            <td>{{ $solicitud->fecha_solicitud }}</td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9">
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
