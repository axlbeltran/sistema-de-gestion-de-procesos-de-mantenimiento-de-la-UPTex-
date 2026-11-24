
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva asignacion | UPTEX</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f6f5; color: #24332c; }
        header { background: #105c40; color: white; padding: 22px 30px; }
        header h2 { margin: 0; }
        main { max-width: 750px; margin: 35px auto; padding: 0 20px; }
        .panel { background: white; padding: 30px; border-radius: 14px; }
        label { display: block; margin: 18px 0 7px; font-weight: bold; }
        select, input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .acciones { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 25px; }
        .boton { background: #16805b; color: white; border: 0; padding: 12px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .gris { background: #64748b; }
        .error { background: #fee2e2; color: #991b1b; padding: 14px; border-radius: 8px; }
        .ayuda { font-size: 13px; color: #64748b; }
    </style>
</head>
<body>
<header><h2>UPTEX | Nueva asignacion</h2></header>
<main>
    <div class="panel">
        <h1>Asignar locker o tarjeton</h1>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('control-recursos.asignaciones.store') }}">
            @csrf

            <label for="usuario_id">Usuario</label>
            <select id="usuario_id" name="usuario_id" required>
                <option value="">Selecciona un usuario</option>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" @selected(old('usuario_id') == $usuario->id)>
                        {{ $usuario->nombre }} {{ $usuario->apellido }} - {{ $usuario->correo }}
                    </option>
                @endforeach
            </select>

            <label for="locker_id">Locker</label>
            <select id="locker_id" name="locker_id">
                <option value="">Sin locker</option>
                @foreach($lockers as $locker)
                    <option value="{{ $locker->id }}" @selected(old('locker_id') == $locker->id)>
                        {{ $locker->numero_locker }} - {{ $locker->ubicacion }}
                    </option>
                @endforeach
            </select>

            <label for="tarjeton_id">Tarjeton</label>
            <select id="tarjeton_id" name="tarjeton_id">
                <option value="">Sin tarjeton</option>
                @foreach($tarjetones as $tarjeton)
                    <option value="{{ $tarjeton->id }}" @selected(old('tarjeton_id') == $tarjeton->id)>
                        {{ $tarjeton->codigo_tarjeton }} - {{ $tarjeton->tipo }}
                    </option>
                @endforeach
            </select>

            <p class="ayuda">Selecciona al menos un locker o un tarjeton. Puedes asignar ambos.</p>

            <label for="es_presencial">Tramite presencial</label>
            <select id="es_presencial" name="es_presencial" required>
                <option value="1" @selected(old('es_presencial', '1') == '1')>Si</option>
                <option value="0" @selected(old('es_presencial') == '0')>No</option>
            </select>

            <label for="pago_sellado_tesoreria">Pago sellado por Tesoreria</label>
            <select id="pago_sellado_tesoreria" name="pago_sellado_tesoreria" required>
                <option value="0" @selected(old('pago_sellado_tesoreria', '0') == '0')>No</option>
                <option value="1" @selected(old('pago_sellado_tesoreria') == '1')>Si</option>
            </select>

            <p class="ayuda">Para registrar una asignacion activa, el pago debe estar sellado.</p>

            <label for="fecha_vencimiento">Fecha de vencimiento</label>
            <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                   min="{{ now()->toDateString() }}"
                   value="{{ old('fecha_vencimiento') }}" required>

            <div class="acciones">
                <button class="boton" type="submit">Guardar asignacion</button>
                <a class="boton gris" href="{{ route('control-recursos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
