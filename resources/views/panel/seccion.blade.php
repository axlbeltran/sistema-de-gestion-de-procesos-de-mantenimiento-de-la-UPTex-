
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} | UPTEX</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        header h2 {
            margin: 0;
            font-size: 20px;
        }

        main {
            max-width: 950px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .contenido {
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,.06);
        }

        .etiqueta {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        h1 {
            margin-top: 20px;
        }

        p {
            line-height: 1.7;
            color: #647067;
        }

        a {
            display: inline-block;
            padding: 12px 20px;
            background: #105c40;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }

        a:hover {
            background: #084b32;
        }
    </style>
</head>
<body>
    <header>
        <h2>UPTEX | Gestión de Mantenimiento</h2>
        <span>{{ auth()->user()->nombre }}</span>
    </header>

    <main>
        <div class="contenido">
            <span class="etiqueta">
                {{ auth()->user()->rol?->nombre_rol ?? 'Sin rol' }}
            </span>

            <h1>{{ $titulo }}</h1>

            <p>{{ $descripcion }}</p>

            <p>
                Has accedido correctamente a una sección
                protegida mediante autenticación y permisos.
            </p>

            <a href="{{ route('dashboard') }}">
                Volver al dashboard
            </a>
        </div>
    </main>
</body>
</html>
