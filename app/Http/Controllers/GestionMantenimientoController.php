<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class GestionMantenimientoController extends Controller
{
    private function rol($usuario): string
    {
        return DB::table('roles')
            ->where('id', $usuario->rol_id)
            ->value('nombre_rol') ?? '';
    }

    private function puedeAdministrar(string $rol): bool
    {
        return in_array($rol, [
            'Super Admin',
            'Responsable de Gestion',
            'Responsable de Area',
        ], true);
    }

    private function consultaSolicitudes($usuario, string $rol)
    {
        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->join('usuarios as u', 's.solicitante_id', '=', 'u.id')
            ->leftJoin(
                'usuarios as t',
                's.tecnico_asignado_id',
                '=',
                't.id'
            )
            ->select(
                's.id',
                's.solicitante_id',
                's.area_id',
                's.tipo_servicio_id',
                's.problema_descripcion',
                's.ubicacion',
                's.nivel_urgencia',
                's.estado',
                's.tecnico_asignado_id',
                's.fecha_solicitud',
                'u.nombre as solicitante_nombre',
                'u.apellido as solicitante_apellido',
                't.nombre as tecnico_nombre',
                't.apellido as tecnico_apellido'
            );

        if ($rol === 'Responsable de Area') {
            $consulta->where('s.area_id', $usuario->area_id);
        } elseif ($rol === 'Tecnico') {
            $consulta->where('s.tecnico_asignado_id', $usuario->id);
        } elseif ($rol === 'Alumno') {
            $consulta->where('s.solicitante_id', $usuario->id);
        }

        return $consulta;
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        $rol = $this->rol($usuario);

        $consulta = $this->consultaSolicitudes($usuario, $rol);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));

            $consulta->where(function ($q) use ($buscar) {
                $q->where('s.id', 'like', "%{$buscar}%")
                    ->orWhere('s.ubicacion', 'like', "%{$buscar}%")
                    ->orWhere('s.problema_descripcion', 'like', "%{$buscar}%")
                    ->orWhere('u.nombre', 'like', "%{$buscar}%")
                    ->orWhere('u.apellido', 'like', "%{$buscar}%");
            });
        }

        if (
            $request->filled('estado') &&
            in_array($request->estado, [
                'Pendiente',
                'En Proceso',
                'Terminado',
            ], true)
        ) {
            $consulta->where('s.estado', $request->estado);
        }

        $solicitudes = $consulta
            ->orderByDesc('s.id')
            ->paginate(10)
            ->withQueryString();

        return view('gestion.index', [
            'solicitudes' => $solicitudes,
            'rol' => $rol,
            'puedeAdministrar' => $this->puedeAdministrar($rol),
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $usuario = $request->user();
        $rol = $this->rol($usuario);

        abort_unless($this->puedeAdministrar($rol), 403);

        $solicitud = $this->consultaSolicitudes($usuario, $rol)
            ->where('s.id', $id)
            ->first();

        abort_unless($solicitud, 404);

        $tecnicos = DB::table('usuarios')
            ->where('rol_id', 4)
            ->select('id', 'nombre', 'apellido', 'area_id')
            ->orderBy('nombre')
            ->get();

        return view('gestion.editar', [
            'solicitud' => $solicitud,
            'tecnicos' => $tecnicos,
            'rol' => $rol,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $usuario = $request->user();
        $rol = $this->rol($usuario);

        abort_unless($this->puedeAdministrar($rol), 403);

        $datos = $request->validate([
            'tecnico_asignado_id' => [
                'nullable',
                'integer',
                Rule::exists('usuarios', 'id')
                    ->where('rol_id', 4),
            ],
            'estado' => [
                'required',
                Rule::in([
                    'Pendiente',
                    'En Proceso',
                    'Terminado',
                ]),
            ],
        ]);

        try {
            DB::transaction(function () use (
                $usuario,
                $rol,
                $datos,
                $id
            ) {
                $solicitud = DB::table('solicitudes_mantenimiento')
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                abort_unless($solicitud, 404);

                if ($rol === 'Responsable de Area') {
                    abort_unless(
                        $usuario->area_id !== null &&
                        (int) $solicitud->area_id ===
                        (int) $usuario->area_id,
                        403
                    );
                }

                $tecnicoNuevo =
                    $datos['tecnico_asignado_id'] ?? null;

                $estadoNuevo = $datos['estado'];

                if (
                    $estadoNuevo !== 'Pendiente' &&
                    $tecnicoNuevo === null
                ) {
                    throw ValidationException::withMessages([
                        'tecnico_asignado_id' =>
                            'Debes asignar un tecnico antes de iniciar o terminar el trabajo.',
                    ]);
                }

                if (
                    $solicitud->estado === 'Terminado' &&
                    (
                        $estadoNuevo !== 'Terminado' ||
                        (string) $tecnicoNuevo !==
                        (string) $solicitud->tecnico_asignado_id
                    )
                ) {
                    throw ValidationException::withMessages([
                        'estado' =>
                            'Una solicitud terminada no puede modificarse desde este formulario.',
                    ]);
                }

                $transiciones = [
                    'Pendiente' => [
                        'Pendiente',
                        'En Proceso',
                    ],
                    'En Proceso' => [
                        'En Proceso',
                        'Terminado',
                    ],
                    'Terminado' => [
                        'Terminado',
                    ],
                ];

                if (!in_array(
                    $estadoNuevo,
                    $transiciones[$solicitud->estado] ?? [],
                    true
                )) {
                    throw ValidationException::withMessages([
                        'estado' =>
                            'El cambio de estado solicitado no esta permitido.',
                    ]);
                }

                $cambioTecnico =
                    (string) $solicitud->tecnico_asignado_id !==
                    (string) $tecnicoNuevo;

                $cambioEstado =
                    $solicitud->estado !== $estadoNuevo;

                if (!$cambioTecnico && !$cambioEstado) {
                    return;
                }

                // AUTORIZACION INICIAL OBLIGATORIA
                $decision = DB::table('autorizaciones_mantenimiento')
                    ->where('solicitud_id', $id)
                    ->where('etapa', 'Autorizacion Inicial')
                    ->orderByDesc('id')
                    ->value('decision');

                $continuacionHistorica =
                    $solicitud->estado === 'En Proceso' &&
                    $solicitud->tecnico_asignado_id !== null &&
                    !$cambioTecnico &&
                    $decision === null;

                if ($decision !== 'Aprobada' && !$continuacionHistorica) {
                    throw ValidationException::withMessages([
                        'estado' => 'La solicitud requiere una autorizacion inicial aprobada antes de asignar o iniciar el mantenimiento.',
                    ]);
                }

                // LEVANTAMIENTO TECNICO APROBADO OBLIGATORIO
                $continuacionPrevia =
                    in_array((int) $id, [2, 4], true) &&
                    $solicitud->estado === 'En Proceso' &&
                    $solicitud->tecnico_asignado_id !== null &&
                    !$cambioTecnico;

                if (!$continuacionPrevia) {
                    $levantamientoAprobado = DB::table('levantamientos_tecnicos')
                        ->where('solicitud_id', $id)
                        ->where('estado', 'Aprobado')
                        ->exists();

                    if (!$levantamientoAprobado) {
                        throw ValidationException::withMessages([
                            'estado' => 'La solicitud requiere un levantamiento tecnico aprobado antes de asignar o iniciar el mantenimiento.',
                        ]);
                    }
                }

                DB::table('solicitudes_mantenimiento')
                    ->where('id', $id)
                    ->update([
                        'tecnico_asignado_id' => $tecnicoNuevo,
                        'estado' => $estadoNuevo,
                    ]);

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $usuario->id,
                    'accion' =>
                        'Actualizacion de solicitud de mantenimiento',
                    'detalles' => json_encode([
                        'solicitud_id' => $id,
                        'tecnico_anterior' =>
                            $solicitud->tecnico_asignado_id,
                        'tecnico_nuevo' => $tecnicoNuevo,
                        'estado_anterior' => $solicitud->estado,
                        'estado_nuevo' => $estadoNuevo,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                /*
                |--------------------------------------------------------------------------
                | NOTIFICACION AL TECNICO
                |--------------------------------------------------------------------------
                */

                if (
                    $cambioTecnico &&
                    $tecnicoNuevo !== null
                ) {
                    DB::table('notificaciones')->insert([
                        'usuario_id' => $tecnicoNuevo,
                        'mensaje' =>
                            "Se te ha asignado la solicitud de mantenimiento #{$id}. " .
                            "Ubicacion: {$solicitud->ubicacion}. " .
                            "Estado: {$estadoNuevo}.",
                        'tipo' => 'Asignacion',
                        'leido' => 0,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | NOTIFICACION AL SOLICITANTE
                |--------------------------------------------------------------------------
                */

                if ($cambioEstado) {
                    DB::table('notificaciones')->insert([
                        'usuario_id' => $solicitud->solicitante_id,
                        'mensaje' =>
                            "Tu solicitud de mantenimiento #{$id} " .
                            "cambio de estado de {$solicitud->estado} " .
                            "a {$estadoNuevo}.",
                        'tipo' => 'Mantenimiento',
                        'leido' => 0,
                    ]);
                }
            });

            return redirect()
                ->route('gestion-mantenimiento.index')
                ->with(
                    'success',
                    "Solicitud #{$id} actualizada correctamente."
                );

        } catch (ValidationException $e) {
            throw $e;

        } catch (HttpExceptionInterface $e) {
            throw $e;

        } catch (Throwable $e) {
            Log::error('Error al actualizar mantenimiento', [
                'solicitud_id' => $id,
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'general' =>
                        'No se pudo actualizar la solicitud. Revisa el registro de errores.',
                ]);
        }
    }
}
