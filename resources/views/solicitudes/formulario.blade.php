
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Solicitud de mantenimiento | UPTEX</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f6f5;
            font-family: Arial, sans-serif;
            color: #24332c;
        }

        header {
            background: #105c40;
            color: white;
            padding: 20px 30px;
        }

        header h2 {
            margin: 0;
        }

        main {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .formulario {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
        }

        h1 {
            margin-top: 0;
        }

        p {
            color: #64748b;
            line-height: 1.6;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 20px;
            font-family: inherit;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 13px 20px;
            background: #105c40;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }

        .cancelar {
            background: #64748b;
        }

        .acciones {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .error {
            color: #b91c1c;
            font-size: 13px;
            margin-top: -14px;
            margin-bottom: 15px;
        }

        .alerta {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alerta ul {
            margin-bottom: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <header>
        <h2>UPTEX | Gestión de Mantenimiento</h2>
    </header>

    <main>
        <div class="formulario">

            @php
                $editando = $solicitud->exists;
            @endphp

            <h1>
                {{ $editando ? 'Editar solicitud' : 'Nueva solicitud' }}
            </h1>

            <p>
                Completa los datos del problema de mantenimiento.
            </p>

            @if ($errors->any())
                <div class="alerta">
                    <strong>No se pudo guardar la solicitud:</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ $editando
                    ? route('solicitudes.update', $solicitud)
                    : route('registro-mantenimiento.store') }}"
            >

                @csrf

                @if ($editando)
                    @method('PUT')
                @endif

                <label for="area_id">
                    Área
                </label>

                <select
                    name="area_id"
                    id="area_id"
                    required
                >
                    <option value="">
                        Selecciona un área
                    </option>

                    @foreach ($areas as $area)
                        <option
                            value="{{ $area->id }}"
                            @selected(
                                old('area_id', $solicitud->area_id) == $area->id
                            )
                        >
                            {{ $area->nombre_area }}
                        </option>
                    @endforeach
                </select>

                @error('area_id')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="tipo_servicio_id">
                    Tipo de servicio
                </label>

                <select
                    name="tipo_servicio_id"
                    id="tipo_servicio_id"
                    required
                >
                    <option value="">
                        Selecciona un servicio
                    </option>

                    @foreach ($tipos as $tipo)
                        <option
                            value="{{ $tipo->id }}"
                            @selected(
                                old('tipo_servicio_id', $solicitud->tipo_servicio_id) == $tipo->id
                            )
                        >
                            {{ $tipo->nombre_servicio }}
                        </option>
                    @endforeach
                </select>

                @error('tipo_servicio_id')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="ubicacion">
                    Ubicación exacta
                </label>

                <input
                    type="text"
                    id="ubicacion"
                    name="ubicacion"
                    maxlength="150"
                    value="{{ old('ubicacion', $solicitud->ubicacion) }}"
                    placeholder="Ejemplo: Edificio A, laboratorio 2"
                    required
                >

                @error('ubicacion')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="nivel_urgencia">
                    Nivel de urgencia
                </label>

                <select
                    name="nivel_urgencia"
                    id="nivel_urgencia"
                    required
                >
                    @foreach (['Baja', 'Media', 'Alta'] as $urgencia)
                        <option
                            value="{{ $urgencia }}"
                            @selected(
                                old(
                                    'nivel_urgencia',
                                    $solicitud->nivel_urgencia ?? 'Media'
                                ) === $urgencia
                            )
                        >
                            {{ $urgencia }}
                        </option>
                    @endforeach
                </select>

                @error('nivel_urgencia')
                    <div class="error">{{ $message }}</div>
                @enderror

                <label for="problema_descripcion">
                    Descripción del problema
                </label>

                <textarea
                    name="problema_descripcion"
                    id="problema_descripcion"
                    minlength="10"
                    maxlength="5000"
                    placeholder="Describe detalladamente el problema..."
                    required
                >{{ old('problema_descripcion', $solicitud->problema_descripcion) }}</textarea>

                @error('problema_descripcion')
                    <div class="error">{{ $message }}</div>
                @enderror

                <div class="acciones">

                    <button
                        type="submit"
                        class="btn"
                    >
                        {{ $editando
                            ? 'Guardar cambios'
                            : 'Registrar solicitud' }}
                    </button>

                    <a
                        href="{{ route('mis-solicitudes') }}"
                        class="btn cancelar"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        </div>
    </main>

</body>
</html>
