
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision de solicitud | UPTEX</title>

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
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .panel {
            background: white;
            padding: 28px;
            border-radius: 14px;
            margin-bottom: 22px;
            box-shadow: 0 4px 18px rgba(0,0,0,.05);
        }

        h1, h2 {
            margin-top: 0;
            color: #105c40;
        }

        .datos {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .dato {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
        }

        .dato strong {
            display: block;
            color: #105c40;
            margin-bottom: 8px;
        }

        .descripcion {
            background: #f8fafc;
            padding: 18px;
            border-radius: 8px;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            resize: vertical;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .acciones {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .boton {
            display: inline-block;
            padding: 13px 20px;
            background: #16805b;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .rojo {
            background: #b91c1c;
        }

        .gris {
            background: #64748b;
        }

        .aviso {
            padding: 15px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 8px;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .error {
            padding: 15px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .historial {
            border-left: 4px solid #16805b;
            background: #f8fafc;
            padding: 18px;
            margin-bottom: 12px;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .subtitulo {
            color: #64748b;
            line-height: 1.6;
        }
    </style>
</head>

<body>

<header>
    <h2>UPTEX | Revision de mantenimiento</h2>
</header>

<main>

    <div class="panel">

        <h1>Solicitud #{{ $solicitud->id }}</h1>

        <p class="subtitulo">
            Revisa la informacion antes de registrar una decision.
        </p>

        <div class="datos">

            <div class="dato">
                <strong>Solicitante</strong>

                {{ $solicitud->solicitante_nombre }}
                {{ $solicitud->solicitante_apellido }}
            </div>

            <div class="dato">
                <strong>Area solicitante</strong>
                {{ $solicitud->nombre_area }}
            </div>

            <div class="dato">
                <strong>Tipo de servicio</strong>
                {{ $solicitud->nombre_servicio }}
            </div>

            <div class="dato">
                <strong>Ubicacion</strong>
                {{ $solicitud->ubicacion }}
            </div>

            <div class="dato">
                <strong>Nivel de urgencia</strong>
                {{ $solicitud->nivel_urgencia }}
            </div>

            <div class="dato">
                <strong>Fecha de solicitud</strong>
                {{ $solicitud->fecha_solicitud }}
            </div>

            <div class="dato">
                <strong>Estado del mantenimiento</strong>
                {{ $solicitud->estado }}
            </div>

            <div class="dato">
                <strong>Estado de autorizacion</strong>
                {{ $solicitud->decision_autorizacion ?? 'Pendiente' }}
            </div>

        </div>

        <br>

        <h2>Descripcion del problema</h2>

        <div class="descripcion">{{ $solicitud->problema_descripcion }}</div>

    </div>

    @if($errors->any())

        <div class="error">

            @foreach($errors->all() as $error)

                <p>{{ $error }}</p>

            @endforeach

        </div>

    @endif

    <div class="panel">

        <h2>Decision de autorizacion</h2>

        @if($solicitud->decision_autorizacion !== null)

            <div class="aviso">
                Esta solicitud ya cuenta con una decision registrada.
            </div>

        @elseif((int) $solicitud->solicitante_id === (int) $usuario->id)

            <div class="aviso">
                No puedes autorizar tu propia solicitud.
            </div>

        @elseif($solicitud->estado !== 'Pendiente' || $solicitud->tecnico_asignado_id !== null)

            <div class="aviso">
                Esta solicitud ya fue asignada o procesada.
            </div>

        @else

            @if((int) $usuario->rol_id === 1)

                <div class="aviso">
                    Estas realizando una autorizacion excepcional
                    como Super Admin. Debes indicar el motivo.
                </div>

            @endif

            <form method="POST"
                  action="{{ route('autorizaciones.resolver', $solicitud->id) }}">

                @csrf

                <label for="observaciones">
                    Observaciones
                </label>

                <textarea
                    id="observaciones"
                    name="observaciones"
                    maxlength="3000"
                    placeholder="Escribe las observaciones correspondientes..."
                >{{ old('observaciones') }}</textarea>

                <p class="subtitulo">
                    Las observaciones son obligatorias al rechazar
                    una solicitud o realizar una autorizacion excepcional.
                </p>

                <div class="acciones">

                    <button
                        type="submit"
                        name="decision"
                        value="Aprobada"
                        class="boton"
                        onclick="return confirm('¿Confirmas la autorizacion de esta solicitud?')"
                    >
                        Autorizar solicitud
                    </button>

                    <button
                        type="submit"
                        name="decision"
                        value="Rechazada"
                        class="boton rojo"
                        onclick="return confirm('¿Confirmas el rechazo de esta solicitud?')"
                    >
                        Rechazar solicitud
                    </button>

                </div>

            </form>

        @endif

    </div>

    <div class="panel">

        <h2>Historial de autorizaciones</h2>

        @forelse($autorizaciones as $autorizacion)

            <div class="historial">

                <strong>{{ $autorizacion->etapa }}</strong>

                <p>
                    Decision: {{ $autorizacion->decision }}
                </p>

                <p>
                    Responsable:
                    {{ $autorizacion->nombre }}
                    {{ $autorizacion->apellido }}
                </p>

                <p>
                    Fecha: {{ $autorizacion->fecha_autorizacion }}
                </p>

                <p>
                    Observaciones:
                    {{ $autorizacion->observaciones ?? 'Sin observaciones' }}
                </p>

            </div>

        @empty

            <p class="subtitulo">
                Todavia no hay autorizaciones registradas.
            </p>

        @endforelse

        <a href="{{ route('autorizaciones.index') }}"
           class="boton gris">
            Volver a autorizaciones
        </a>

    </div>

</main>

</body>
</html>
