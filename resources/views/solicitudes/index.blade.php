<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de mantenimiento | UPTEX</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f6f5;
            color: #25352c;
        }

        header {
            background: #105c40;
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        header h2 { margin: 0; }

        main {
            max-width: 1250px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.05);
            margin-bottom: 22px;
        }

        .cabecera, .filtros {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filtros { justify-content: flex-start; }

        h1 { margin-top: 0; }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            background: #105c40;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-secundario { background: #64748b; }

        input, select {
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        input { flex: 1; min-width: 180px; }

        .tabla {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 14px;
        }

        th { background: #f0f7f3; }

        .estado {
            display: inline-block;
            border-radius: 20px;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: bold;
            background: #e5e7eb;
        }

        .pendiente { background: #fef3c7; color: #92400e; }
        .proceso { background: #dbeafe; color: #1d4ed8; }
        .terminado { background: #dcfce7; color: #166534; }

        .alerta {
            padding: 14px;
            background: #dcfce7;
            color: #166534;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .paginacion { margin-top: 20px; }

        .paginacion a {
            display: inline-block;
            margin: 5px;
            color: #105c40;
        }

        @media(max-width: 650px) {
            header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
<header>
    <h2>UPTEX | Mantenimiento</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-secundario">
        Volver al panel
    </a>
</header>

<main>
    <div class="panel">
        <div class="cabecera">
            <div>
                <h1>Solicitudes de mantenimiento</h1>
                <p>Consulta y seguimiento de solicitudes registradas.</p>
            </div>

            <a href="{{ route('solicitudes.create') }}" class="btn">
                + Nueva solicitud
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alerta">
            {{ session('success') }}
        </div>
    @endif

    <div class="panel">
        <form method="GET" action="{{ route('mis-solicitudes') }}"
              class="filtros">

            <input
                type="text"
                id="buscar"
                name="buscar"
                value="{{ request('buscar') }}"
                placeholder="Buscar por problema o ubicación"
            >

            <select name="estado" id="estado">
                <option value="">Todos los estados</option>
                @foreach(['Pendiente', 'En Proceso', 'Terminado'] as $estado)
                    <option value="{{ $estado }}"
                        @selected(request('estado') === $estado)>
                        {{ $estado }}
                    </option>
                @endforeach
            </select>

            <button class="btn" type="submit">Buscar</button>

            <a href="{{ route('mis-solicitudes') }}"
               class="btn btn-secundario">Limpiar</a>
        </form>
    </div>

    <div class="panel">
<div class="tabla" id="resultados-solicitudes">
    <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Solicitante</th>
                        <th>Área</th>
                        <th>Servicio</th>
                        <th>Problema / Ubicación</th>
                        <th>Urgencia</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($solicitudes as $solicitud)
                        <tr>
                            <td>#{{ $solicitud->id }}</td>

                            <td>
                                {{ $solicitud->solicitante?->nombre }}
                                {{ $solicitud->solicitante?->apellido }}
                            </td>

                            <td>
                                {{ $solicitud->area?->nombre_area ?? 'Sin área' }}
                            </td>

                            <td>
                                {{ $solicitud->tipoServicio?->nombre_servicio ?? 'Sin servicio' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $solicitud->problema_descripcion }}
                                </strong>
                                <p>{{ $solicitud->ubicacion }}</p>
                            </td>

                            <td>{{ $solicitud->nivel_urgencia }}</td>

                            <td>
                                <span class="estado
                                    {{ $solicitud->estado === 'Pendiente' ? 'pendiente' : '' }}
                                    {{ $solicitud->estado === 'En Proceso' ? 'proceso' : '' }}
                                    {{ $solicitud->estado === 'Terminado' ? 'terminado' : '' }}">
                                    {{ $solicitud->estado }}
                                </span>
                            </td>

                            <td>
                                {{ $solicitud->fecha_solicitud?->format('d/m/Y') }}
                            </td>

                            <td>
                                @if(
                                    $solicitud->estado === 'Pendiente' &&
                                    (int) $solicitud->solicitante_id ===
                                    (int) auth()->id()
                                )
                                    <a href="{{ route('solicitudes.edit', $solicitud) }}">
                                        Editar
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
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

<div class="paginacion" id="paginacion-solicitudes">            
            @if($solicitudes->onFirstPage())
                <span>Anterior</span>
            @else
                <a href="{{ $solicitudes->previousPageUrl() }}">
                    Anterior
                </a>
            @endif

            <span>
                Página {{ $solicitudes->currentPage() }}
                de {{ $solicitudes->lastPage() }}
            </span>

            @if($solicitudes->hasMorePages())
                <a href="{{ $solicitudes->nextPageUrl() }}">
                    Siguiente
                </a>
            @else
                <span>Siguiente</span>
            @endif
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buscar = document.getElementById('buscar');
    const estado = document.getElementById('estado');
    const formulario = buscar.closest('form');
    const tabla = document.getElementById('resultados-solicitudes');
    const paginacion = document.getElementById('paginacion-solicitudes');

    let temporizador;
    let controlador;

    async function cargarResultados(url) {
        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        tabla.style.opacity = '0.5';

        try {
            const respuesta = await fetch(url, {
                signal: controlador.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!respuesta.ok) {
                throw new Error('Error HTTP ' + respuesta.status);
            }

            const html = await respuesta.text();
            const documento = new DOMParser()
                .parseFromString(html, 'text/html');

            const nuevaTabla = documento.getElementById(
                'resultados-solicitudes'
            );

            const nuevaPaginacion = documento.getElementById(
                'paginacion-solicitudes'
            );

            if (!nuevaTabla || !nuevaPaginacion) {
                throw new Error('No se encontraron los resultados.');
            }

            tabla.innerHTML = nuevaTabla.innerHTML;
            paginacion.innerHTML = nuevaPaginacion.innerHTML;

            window.history.replaceState({}, '', url);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
                alert('No se pudieron actualizar las solicitudes.');
            }
        } finally {
            if (!controlador.signal.aborted) {
                tabla.style.opacity = '1';
            }
        }
    }

    function actualizarBusqueda() {
        clearTimeout(temporizador);

        temporizador = setTimeout(function () {
            const url = new URL(formulario.action);

            if (buscar.value.trim()) {
                url.searchParams.set('buscar', buscar.value.trim());
            }

            if (estado.value) {
                url.searchParams.set('estado', estado.value);
            }

            cargarResultados(url.toString());
        }, 300);
    }

    buscar.addEventListener('input', actualizarBusqueda);
    estado.addEventListener('change', actualizarBusqueda);

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        clearTimeout(temporizador);
        actualizarBusqueda();
    });

    paginacion.addEventListener('click', function (evento) {
        const enlace = evento.target.closest('a');

        if (!enlace) {
            return;
        }

        evento.preventDefault();
        cargarResultados(enlace.href);
    });
});
</script>

</body>
</html>
