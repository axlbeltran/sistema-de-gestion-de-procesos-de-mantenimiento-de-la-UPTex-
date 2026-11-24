
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | UPTEX</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #062b20, #126548);
            padding: 20px;
        }

        .login {
            width: 100%;
            max-width: 420px;
            padding: 38px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 18px 50px rgba(0,0,0,.25);
        }

        .logo {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #116445;
        }

        h1 {
            text-align: center;
            font-size: 23px;
            margin: 20px 0 8px;
        }

        .subtitulo {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 28px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #116445;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #116445;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover { background: #084b32; }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .pie {
            text-align: center;
            color: #777;
            margin-top: 25px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login">
        <div class="logo">UPTEX</div>

        <h1>Sistema de Gestión</h1>
        <p class="subtitulo">
            Procesos de Mantenimiento
        </p>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.procesar') }}">
            @csrf

            <label for="correo">Correo electrónico</label>
            <input
                type="email"
                id="correo"
                name="correo"
                value="{{ old('correo') }}"
                placeholder="usuario@uptex.edu.mx"
                autocomplete="username"
                required
            >

            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Ingresa tu contraseña"
                autocomplete="current-password"
                required
            >

            <button type="submit">Iniciar sesión</button>
        </form>

        <div class="pie">
            Universidad Politécnica de Texcoco
        </div>
    </div>
</body>
</html>
