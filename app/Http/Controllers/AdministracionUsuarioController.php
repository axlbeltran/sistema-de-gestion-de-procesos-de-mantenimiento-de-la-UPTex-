<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdministracionUsuarioController extends Controller
{
    private function verificarAcceso(Request $request): void
    {
        abort_unless(
            $request->user() &&
            (int) $request->user()->rol_id === 1,
            403
        );
    }

    private function roles()
    {
        return DB::table('roles')
            ->orderBy('id')
            ->get();
    }

    private function areas()
    {
        return DB::table('areas')
            ->orderBy('nombre_area')
            ->get();
    }

    public function index(Request $request)
    {
        $this->verificarAcceso($request);

        $consulta = DB::table('usuarios as u')
            ->join('roles as r', 'u.rol_id', '=', 'r.id')
            ->leftJoin('areas as a', 'u.area_id', '=', 'a.id')
            ->select(
                'u.id',
                'u.nombre',
                'u.apellido',
                'u.correo',
                'u.matricula_o_empleado',
                'r.nombre_rol',
                'a.nombre_area'
            );

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));

            $consulta->where(function ($q) use ($buscar) {
                $q->where('u.nombre', 'like', "%{$buscar}%")
                    ->orWhere('u.apellido', 'like', "%{$buscar}%")
                    ->orWhere('u.correo', 'like', "%{$buscar}%")
                    ->orWhere('u.matricula_o_empleado', 'like', "%{$buscar}%")
                    ->orWhere('r.nombre_rol', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $consulta
            ->orderBy('u.id')
            ->paginate(10)
            ->withQueryString();

        return view('administracion.usuarios.index', [
            'usuarios' => $usuarios,
        ]);
    }

    public function create(Request $request)
    {
        $this->verificarAcceso($request);

        return view('administracion.usuarios.crear', [
            'roles' => $this->roles(),
            'areas' => $this->areas(),
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarAcceso($request);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'correo'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'area_id' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id'),
            ],
            'matricula_o_empleado' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('usuarios', 'matricula_o_empleado'),
            ],
        ]);

        try {
            DB::transaction(function () use ($datos, $request) {
                $nuevoId = DB::table('usuarios')->insertGetId([
                    'nombre' => trim($datos['nombre']),
                    'apellido' => trim($datos['apellido']),
                    'correo' => trim($datos['correo']),
                    'password' => Hash::make($datos['password']),
                    'rol_id' => $datos['rol_id'],
                    'area_id' => $datos['area_id'] ?? null,
                    'matricula_o_empleado' =>
                        $datos['matricula_o_empleado'] ?? null,
                ]);

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $request->user()->id,
                    'accion' => 'Registro de usuario',
                    'detalles' => json_encode([
                        'usuario_id' => $nuevoId,
                        'rol_id' => $datos['rol_id'],
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            });

            return redirect()
                ->route('administracion.usuarios.index')
                ->with('success', 'Usuario registrado correctamente.');

        } catch (Throwable $e) {
            Log::error('Error al registrar usuario', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except(
                    'password',
                    'password_confirmation'
                ))
                ->withErrors([
                    'general' => 'No se pudo registrar el usuario.',
                ]);
        }
    }

    public function edit(Request $request, int $id)
    {
        $this->verificarAcceso($request);

        $usuario = DB::table('usuarios')
            ->where('id', $id)
            ->first();

        abort_unless($usuario, 404);

        return view('administracion.usuarios.editar', [
            'usuario' => $usuario,
            'roles' => $this->roles(),
            'areas' => $this->areas(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->verificarAcceso($request);

        $usuario = DB::table('usuarios')
            ->where('id', $id)
            ->first();

        abort_unless($usuario, 404);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'correo')->ignore($id),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'area_id' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id'),
            ],
            'matricula_o_empleado' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(
                    'usuarios',
                    'matricula_o_empleado'
                )->ignore($id),
            ],
        ]);

        if (
            (int) $usuario->id ===
            (int) $request->user()->id &&
            (int) $datos['rol_id'] !== 1
        ) {
            throw ValidationException::withMessages([
                'rol_id' =>
                    'No puedes quitarte tu propio rol de Super Admin.',
            ]);
        }

        try {
            DB::transaction(function () use ($datos, $id, $request) {
                $actualizacion = [
                    'nombre' => trim($datos['nombre']),
                    'apellido' => trim($datos['apellido']),
                    'correo' => trim($datos['correo']),
                    'rol_id' => $datos['rol_id'],
                    'area_id' => $datos['area_id'] ?? null,
                    'matricula_o_empleado' =>
                        $datos['matricula_o_empleado'] ?? null,
                ];

                if (!empty($datos['password'])) {
                    $actualizacion['password'] =
                        Hash::make($datos['password']);
                }

                DB::table('usuarios')
                    ->where('id', $id)
                    ->update($actualizacion);

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $request->user()->id,
                    'accion' => 'Actualizacion de usuario',
                    'detalles' => json_encode([
                        'usuario_id' => $id,
                        'rol_id' => $datos['rol_id'],
                        'password_actualizada' =>
                            !empty($datos['password']),
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            });

            return redirect()
                ->route('administracion.usuarios.index')
                ->with('success', 'Usuario actualizado correctamente.');

        } catch (Throwable $e) {
            Log::error('Error al actualizar usuario', [
                'usuario_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->except(
                    'password',
                    'password_confirmation'
                ))
                ->withErrors([
                    'general' => 'No se pudo actualizar el usuario.',
                ]);
        }
    }
}
