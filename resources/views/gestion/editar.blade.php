<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar solicitud | UPTEX</title>

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
            padding: 20px 30px;
        }

        header h2 { margin: 0; }

        main {
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,.06);
        }

        h1 { margin-top: 0; }

        .descripcion {
            color: #64748b;
            line-height: 1.6;
        }

        .dato {
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .dato strong {
            display: block;
            margin-bottom: 5px;
            color: #105c40;
        }

        hr {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 28px 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 9px;
        }

        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 22px;
            background: white;
        }

        .btn {
            display: inline-block;
            padding: 13px 20px;
            border: none;
            border-radius: 8px;
            background: #105c40;
            color: white;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-gris { background: #64748b; }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .alerta {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alerta ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .nota {
            background: #eff6ff;
            color: #1e40af;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .bloqueado {
            background: #f1f5f9;
            color: #475569;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Gestión de Mantenimiento</h2>
</header>

<main>
    <div class="panel">

        <h1>
            Gestionar solicitud #{{ $solicitud->id }}
        </h1>

        <p class="descripcion">
            Asigna un técnico y administra el avance del trabajo.
        </p>

        @if ($errors->any())
            <div class="alerta">
                <strong>No se pudo guardar:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dato">
            <strong>Solicitante</strong>
            {{ $solicitud->solicitante_nombre }}
            {{ $solicitud->solicitante_apellido }}
        </div>

        <div class="dato">
            <strong>Ubicación</strong>
            {{ $solicitud->ubicacion }}
        </div>

        <div class="dato">
            <strong>Descripción del problema</strong>
            {{ $solicitud->problema_descripcion }}
        </div>

        <div class="dato">
            <strong>Nivel de urgencia</strong>
            {{ $solicitud->nivel_urgencia }}
        </div>

        <div class="dato">
            <strong>Estado actual</strong>
            {{ $solicitud->estado }}
        </div>

        <div class="dato">
            <strong>Técnico actual</strong>

            @if ($solicitud->tecnico_asignado_id)
                {{ $solicitud->tecnico_nombre }}
                {{ $solicitud->tecnico_apellido }}
            @else
                Sin técnico asignado
            @endif
        </div>

        <hr>

        @if ($solicitud->estado === 'Terminado')

            <div class="bloqueado">
                Esta solicitud ya está terminada.
                No se permiten modificaciones desde este formulario.
            </div>

            <a
                href="{{ route('gestion-mantenimiento.index') }}"
                class="btn btn-gris"
            >
                Volver al listado
            </a>

        @else

            <div class="nota">
                Para pasar una solicitud a En Proceso o Terminado
                debe tener un técnico asignado.
                Los estados avanzan de forma secuencial.
            </div>

            <form
                method="POST"
                action="{{ route(
                    'gestion-mantenimiento.update',
                    $solicitud->id
                ) }}"
            >

                @csrf
                @method('PUT')

                <label for="tecnico_asignado_id">
                    Técnico responsable
                </label>

                <select
                    name="tecnico_asignado_id"
                    id="tecnico_asignado_id"
                >
                    <option value="">
                        Sin técnico asignado
                    </option>

                    @foreach ($tecnicos as $tecnico)
                        <option
                            value="{{ $tecnico->id }}"
                            @selected(
                                old(
                                    'tecnico_asignado_id',
                                    $solicitud->tecnico_asignado_id
                                ) == $tecnico->id
                            )
                        >
                            {{ $tecnico->nombre }}
                            {{ $tecnico->apellido }}
                            (Área {{ $tecnico->area_id ?? 'Sin área' }})
                        </option>
                    @endforeach
                </select>

                <label for="estado">
                    Estado de la solicitud
                </label>

                <select
                    name="estado"
                    id="estado"
                    required
                >
                    @php
                        $estadosPermitidos = match ($solicitud->estado) {
                            'Pendiente' => [
                                'Pendiente',
                                'En Proceso'
                            ],
                            'En Proceso' => [
                                'En Proceso',
                                'Terminado'
                            ],
                            default => [
                                'Terminado'
                            ]
                        };
                    @endphp

                    @foreach ($estadosPermitidos as $estado)
                        <option
                            value="{{ $estado }}"
                            @selected(
                                old('estado', $solicitud->estado) === $estado
                            )
                        >
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>

                <div class="acciones">

                    <button type="submit" class="btn">
                        Guardar cambios
                    </button>

                    <a
                        href="{{ route('gestion-mantenimiento.index') }}"
                        class="btn btn-gris"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        @endif

    </div>
</main>

</body>
</html>
