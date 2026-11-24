
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo usuario | UPTEX</title>
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
            max-width: 750px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .panel {
            background: white;
            border-radius: 14px;
            padding: 30px;
        }
        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
        }
        .boton {
            display: inline-block;
            padding: 12px 18px;
            border: 0;
            border-radius: 8px;
            background: #16805b;
            color: white;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }
        .secundario { background: #64748b; }
        .acciones {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .errores {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
        }
        .ayuda {
            font-size: 13px;
            color: #64748b;
        }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Nuevo usuario</h2>
</header>

<main>
    <div class="panel">
        <h1>Registrar usuario</h1>
        <p>Completa los datos para crear una cuenta.</p>

        @if($errors->any())
            <div class="errores">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('administracion.usuarios.store') }}">
            @csrf

            <label for="nombre">Nombre</label>
            <input
                id="nombre"
                name="nombre"
                maxlength="100"
                value="{{ old('nombre') }}"
                required
            >

            <label for="apellido">Apellido</label>
            <input
                id="apellido"
                name="apellido"
                maxlength="100"
                value="{{ old('apellido') }}"
                required
            >

            <label for="correo">Correo electronico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                maxlength="100"
                value="{{ old('correo') }}"
                required
            >

            <label for="rol_id">Rol</label>
            <select id="rol_id" name="rol_id" required>
                <option value="">Selecciona un rol</option>

                @foreach($roles as $rol)
                    <option
                        value="{{ $rol->id }}"
                        @selected(old('rol_id') == $rol->id)
                    >
                        {{ $rol->nombre_rol }}
                    </option>
                @endforeach
            </select>

            <label for="area_id">Area</label>
            <select id="area_id" name="area_id">
                <option value="">Sin area asignada</option>

                @foreach($areas as $area)
                    <option
                        value="{{ $area->id }}"
                        @selected(old('area_id') == $area->id)
                    >
                        {{ $area->nombre_area }}
                    </option>
                @endforeach
            </select>

            <label for="matricula_o_empleado">
                Matricula o numero de empleado
            </label>
            <input
                id="matricula_o_empleado"
                name="matricula_o_empleado"
                maxlength="50"
                value="{{ old('matricula_o_empleado') }}"
            >

            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <label for="password_confirmation">
                Confirmar contraseña
            </label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                minlength="8"
                autocomplete="new-password"
                required
            >

            <p class="ayuda">
                La contraseña debe tener al menos 8 caracteres.
            </p>

            <div class="acciones">
                <button class="boton" type="submit">
                    Guardar usuario
                </button>

                <a class="boton secundario"
                   href="{{ route('administracion.usuarios.index') }}">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
