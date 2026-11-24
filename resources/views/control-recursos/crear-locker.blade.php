
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar locker | UPTEX</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f6f5; color: #24332c; }
        header { background: #105c40; color: white; padding: 22px 30px; }
        header h2 { margin: 0; }
        main { max-width: 700px; margin: 35px auto; padding: 0 20px; }
        .panel { background: white; padding: 30px; border-radius: 14px; }
        label { display: block; margin: 18px 0 7px; font-weight: bold; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .acciones { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 25px; }
        .boton { background: #16805b; color: white; border: 0; padding: 12px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; cursor: pointer; }
        .gris { background: #64748b; }
        .error { background: #fee2e2; color: #991b1b; padding: 14px; border-radius: 8px; }
    </style>
</head>
<body>
<header><h2>UPTEX | Registrar locker</h2></header>
<main>
    <div class="panel">
        <h1>Nuevo locker</h1>

        @if($errors->any())
            <div class="error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('control-recursos.lockers.store') }}">
            @csrf

            <label for="numero_locker">Numero de locker</label>
            <input id="numero_locker" name="numero_locker" maxlength="20"
                   placeholder="Ejemplo: L-006" value="{{ old('numero_locker') }}" required>

            <label for="ubicacion">Ubicacion</label>
            <input id="ubicacion" name="ubicacion" maxlength="100"
                   placeholder="Ejemplo: Pasillo Edificio C" value="{{ old('ubicacion') }}" required>

            <label for="estado">Estado inicial</label>
            <select id="estado" name="estado" required>
                <option value="Disponible" @selected(old('estado') === 'Disponible')>Disponible</option>
                <option value="Mantenimiento" @selected(old('estado') === 'Mantenimiento')>Mantenimiento</option>
            </select>

            <div class="acciones">
                <button class="boton" type="submit">Guardar locker</button>
                <a class="boton gris" href="{{ route('control-recursos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
