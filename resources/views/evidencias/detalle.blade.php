
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evidencias de solicitud | UPTEX</title>

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
            max-width: 1050px;
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

        .subtitulo {
            color: #64748b;
            line-height: 1.6;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
            margin-bottom: 25px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            background: #105c40;
            color: white;
            padding: 12px 18px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-gris { background: #64748b; }

        label {
            display: block;
            font-weight: bold;
            margin: 20px 0 9px;
        }

        input[type="file"],
        input[type="text"] {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        .nota {
            background: #eff6ff;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .exito {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .galeria {
            display: grid;
            grid-template-columns: repeat(
                auto-fill,
                minmax(230px, 1fr)
            );
            gap: 20px;
        }

        .evidencia {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .evidencia img {
            width: 100%;
            height: 190px;
            object-fit: cover;
            display: block;
        }

        .contenido {
            padding: 15px;
        }

        .contenido p {
            margin: 0 0 10px;
            overflow-wrap: anywhere;
        }

        .fecha {
            color: #64748b;
            font-size: 12px;
        }

        .vacio {
            color: #64748b;
            padding: 15px 0;
        }

        .subir {
            margin-top: 22px;
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Evidencias de Mantenimiento</h2>
</header>

<main>

    <div class="cabecera">
        <div>
            <h1>
                Evidencias de solicitud #{{ $solicitud->id }}
            </h1>

            <p class="subtitulo">
                Estado actual: {{ $solicitud->estado }}
            </p>
        </div>

        <a href="{{ route('evidencias.index') }}"
           class="btn btn-gris">
            Volver a trabajos
        </a>
    </div>

    @if (session('success'))
        <div class="exito">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error">
            <strong>No se pudo subir la fotografía:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <h2>Información del mantenimiento</h2>

        <p>
            <strong>Ubicación:</strong>
            {{ $solicitud->ubicacion }}
        </p>

        <p>
            <strong>Problema:</strong>
            {{ $solicitud->problema_descripcion }}
        </p>

        <p>
            <strong>Urgencia:</strong>
            {{ $solicitud->nivel_urgencia }}
        </p>
    </div>

    <div class="panel">

        <h2>Fotografías registradas</h2>

        @if ($evidencias->isEmpty())
            <p class="vacio">
                Esta solicitud todavía no tiene evidencias fotográficas.
            </p>
        @else

            <div class="galeria">

                @foreach ($evidencias as $evidencia)
                    <div class="evidencia">

                        <a
                            href="{{ route(
                                'evidencias.imagen',
                                $evidencia->id
                            ) }}"
                            target="_blank"
                            rel="noopener"
                        >
                            <img
                                src="{{ route(
                                    'evidencias.imagen',
                                    $evidencia->id
                                ) }}"
                                alt="Evidencia #{{ $evidencia->id }}"
                                loading="lazy"
                            >
                        </a>

                        <div class="contenido">
                            <p>
                                {{ $evidencia->descripcion_evidencia
                                    ?: 'Sin descripción' }}
                            </p>

                            <span class="fecha">
                                Subida:
                                {{ $evidencia->fecha_subida }}
                            </span>
                        </div>

                    </div>
                @endforeach

            </div>

        @endif

    </div>

    @if ($puedeSubir)

        <div class="panel">
            <h2>Subir nueva evidencia</h2>

            <div class="nota">
                Puedes subir fotografías JPG, PNG o WEBP
                de hasta 5 MB cada una.
            </div>

            <form
                method="POST"
                action="{{ route(
                    'evidencias.store',
                    $solicitud->id
                ) }}"
                enctype="multipart/form-data"
            >

                @csrf

                <label for="fotografia">
                    Seleccionar fotografía
                </label>

                <input
                    type="file"
                    id="fotografia"
                    name="fotografia"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    required
                >

                <label for="descripcion_evidencia">
                    Descripción de la evidencia
                </label>

                <input
                    type="text"
                    id="descripcion_evidencia"
                    name="descripcion_evidencia"
                    maxlength="150"
                    value="{{ old('descripcion_evidencia') }}"
                    placeholder="Ejemplo: Reparación del contacto eléctrico"
                >

                <button type="submit" class="btn subir">
                    Subir fotografía
                </button>

            </form>
        </div>

    @else

        <div class="nota">
            Solo el técnico asignado puede subir evidencias
            mientras la solicitud esté En Proceso.
            Puedes consultar las fotografías existentes.
        </div>

    @endif

</main>

</body>
</html>
