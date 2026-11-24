<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion de usuarios | UPTEX</title>
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
            max-width: 1200px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .panel {
            background: white;
            padding: 25px;
            border-radius: 14px;
            margin-bottom: 20px;
        }
        .acciones {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .boton {
            display: inline-block;
            padding: 12px 18px;
            background: #16805b;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        .secundario { background: #64748b; }
        .buscador {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        input {
            flex: 1;
            min-width: 200px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }
        .tabla {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background: #ecfdf5;
            color: #065f46;
        }
        .editar {
            color: #047857;
            font-weight: bold;
            text-decoration: none;
        }
        .mensaje {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .paginacion { margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h2>UPTEX | Administracion de usuarios</h2>
</header>

<main>
    <div class="panel">
        <div class="acciones">
            <div>
                <h1>Usuarios registrados</h1>
                <p>Administracion de cuentas del sistema.</p>
            </div>

            <div>
                <a class="boton secundario"
                   href="{{ route('dashboard') }}">
                    Volver al panel
                </a>

                <a class="boton"
                   href="{{ route('administracion.usuarios.create') }}">
                    + Nuevo usuario
                </a>
            </div>
        </div>

        <form method="GET"
              action="{{ route('administracion.usuarios.index') }}"
              class="buscador">

            <input
                type="search"
                name="buscar"
                placeholder="Buscar nombre, correo, matricula o rol"
                value="{{ request('buscar') }}"
            >

            <button class="boton" type="submit">
                Buscar
            </button>

            <a class="boton secundario"
               href="{{ route('administracion.usuarios.index') }}">
                Limpiar
            </a>
        </form>
    </div>

    @if(session('success'))
        <div class="mensaje">
            {{ session('success') }}
        </div>
    @endif

    <div class="panel">
        <div class="tabla">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Area</th>
                        <th>Matricula / Empleado</th>
                        <th>Accion</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>
                                {{ $usuario->nombre }}
                                {{ $usuario->apellido }}
                            </td>
                            <td>{{ $usuario->correo }}</td>
                            <td>{{ $usuario->nombre_rol }}</td>
                            <td>
                                {{ $usuario->nombre_area ?? 'Sin area' }}
                            </td>
                            <td>
                                {{ $usuario->matricula_o_empleado ?? 'N/A' }}
                            </td>
                            <td>
                                <a class="editar"
                                   href="{{ route('administracion.usuarios.edit', $usuario->id) }}">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="paginacion">
            {{ $usuarios->links() }}
        </div>
    </div>
</main>

</body>
</html>
