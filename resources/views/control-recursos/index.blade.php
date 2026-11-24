
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de recursos | UPTEX</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f6f5; color: #24332c; }
        header { background: #105c40; color: white; padding: 22px 30px; }
        header h2 { margin: 0; }
        main { max-width: 1250px; margin: 30px auto; padding: 0 18px; }
        .panel { background: white; padding: 24px; border-radius: 13px; margin-bottom: 22px; }
        .acciones { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .boton { display: inline-block; background: #16805b; color: white; border: 0; padding: 11px 15px; border-radius: 8px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .gris { background: #64748b; }
        .rojo { background: #b91c1c; }
        input { padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .tabla { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #ecfdf5; color: #065f46; }
        .mensaje { background: #d1fae5; color: #065f46; padding: 14px; border-radius: 8px; margin-bottom: 15px; }
        .error { background: #fee2e2; color: #991b1b; padding: 14px; border-radius: 8px; margin-bottom: 15px; }
        .paginacion { margin-top: 18px; }
        form.inline { display: inline; }
        h1 { margin-top: 0; }
        h2 { margin-top: 0; }
        small { color: #64748b; }
    </style>
</head>
<body>
<header>
    <h2>UPTEX | Control de lockers y tarjetones</h2>
</header>

<main>
    <div class="panel">
        <h1>Administracion de recursos</h1>
        <p>Registro, asignacion y seguimiento de lockers y tarjetones.</p>

        <div class="acciones">
            <a class="boton gris" href="{{ route('dashboard') }}">Volver al panel</a>
            <a class="boton" href="{{ route('control-recursos.lockers.create') }}">+ Registrar locker</a>
            <a class="boton" href="{{ route('control-recursos.tarjetones.create') }}">+ Registrar tarjeton</a>
            <a class="boton" href="{{ route('control-recursos.asignaciones.create') }}">+ Nueva asignacion</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mensaje">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="panel">
        <h2>Buscar recursos</h2>
        <form method="GET" action="{{ route('control-recursos.index') }}" class="acciones">
            <input type="search" name="buscar" placeholder="Numero, codigo, ubicacion o tipo" value="{{ request('buscar') }}" style="flex:1;min-width:220px;">
            <button class="boton" type="submit">Buscar</button>
            <a class="boton gris" href="{{ route('control-recursos.index') }}">Limpiar</a>
        </form>
    </div>

    <div class="panel">
        <h2>Lockers</h2>
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numero</th>
                        <th>Ubicacion</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lockers as $locker)
                        <tr>
                            <td>{{ $locker->id }}</td>
                            <td>{{ $locker->numero_locker }}</td>
                            <td>{{ $locker->ubicacion }}</td>
                            <td>{{ $locker->estado }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No hay lockers para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="paginacion">{{ $lockers->links() }}</div>
    </div>

    <div class="panel">
        <h2>Tarjetones</h2>
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tarjetones as $tarjeton)
                        <tr>
                            <td>{{ $tarjeton->id }}</td>
                            <td>{{ $tarjeton->codigo_tarjeton }}</td>
                            <td>{{ $tarjeton->tipo }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No hay tarjetones para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="paginacion">{{ $tarjetones->links() }}</div>
    </div>

    <div class="panel">
        <h2>Historial de asignaciones</h2>
        <small>Las asignaciones vencidas y canceladas se conservan para consulta.</small>

        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Locker</th>
                        <th>Tarjeton</th>
                        <th>Presencial</th>
                        <th>Pago sellado</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asignaciones as $asignacion)
                        <tr>
                            <td>{{ $asignacion->id }}</td>
                            <td>{{ $asignacion->nombre }} {{ $asignacion->apellido }}</td>
                            <td>{{ $asignacion->numero_locker ?? 'N/A' }}</td>
                            <td>{{ $asignacion->codigo_tarjeton ?? 'N/A' }}</td>
                            <td>{{ $asignacion->es_presencial ? 'Si' : 'No' }}</td>
                            <td>{{ $asignacion->pago_sellado_tesoreria ? 'Si' : 'No' }}</td>
                            <td>{{ $asignacion->fecha_vencimiento }}</td>
                            <td>{{ $asignacion->estado }}</td>
                            <td>
                                @if($asignacion->estado === 'Activo')
                                    <form class="inline" method="POST"
                                          action="{{ route('control-recursos.asignaciones.cancelar', $asignacion->id) }}"
                                          onsubmit="return confirm('¿Cancelar esta asignacion?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="boton rojo">Cancelar</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No hay asignaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="paginacion">{{ $asignaciones->links() }}</div>
    </div>
</main>
</body>
</html>
