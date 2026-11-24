<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class FinalizacionMantenimientoController extends Controller
{
    private function rol($usuario): string
    {
        return DB::table('roles')
            ->where('id', $usuario->rol_id)
            ->value('nombre_rol') ?? '';
    }

    public function trabajos(Request $request)
    {
        $usuario = $request->user();

        abort_unless(
            $this->rol($usuario) === 'Tecnico',
            403
        );

        $trabajos = DB::table('solicitudes_mantenimiento')
            ->where('tecnico_asignado_id', $usuario->id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('finalizacion.trabajos', [
            'trabajos' => $trabajos,
        ]);
    }

    public function finalizar(Request $request, int $id)
    {
        $usuario = $request->user();

        abort_unless(
            $this->rol($usuario) === 'Tecnico',
            403
        );

        try {
            DB::transaction(function () use ($usuario, $id) {

                $solicitud = DB::table('solicitudes_mantenimiento')
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                abort_unless($solicitud, 404);

                abort_unless(
                    $solicitud->tecnico_asignado_id !== null &&
                    (int) $solicitud->tecnico_asignado_id ===
                    (int) $usuario->id,
                    403
                );

                if ($solicitud->estado !== 'En Proceso') {
                    throw ValidationException::withMessages([
                        'estado' =>
                            'Solo se pueden finalizar trabajos En Proceso.',
                    ]);
                }

                DB::table('solicitudes_mantenimiento')
                    ->where('id', $id)
                    ->update([
                        'estado' => 'Terminado',
                        'fecha_actualizacion' => now(),
                    ]);

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $usuario->id,
                    'accion' => 'Finalizacion de mantenimiento',
                    'detalles' => json_encode([
                        'solicitud_id' => $id,
                        'estado_anterior' => $solicitud->estado,
                        'estado_nuevo' => 'Terminado',
                        'tecnico_id' => $usuario->id,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                $nombreTecnico = trim(
                    $usuario->nombre . ' ' . $usuario->apellido
                );

                $mensaje =
                    "Tu solicitud de mantenimiento #{$id} " .
                    "ha sido finalizada correctamente. " .
                    "Ubicacion: {$solicitud->ubicacion}. " .
                    "Tecnico responsable: {$nombreTecnico}. " .
                    "Estado actual: Terminado.";

                DB::table('notificaciones')->insert([
                    'usuario_id' => $solicitud->solicitante_id,
                    'mensaje' => $mensaje,
                    'tipo' => 'Mantenimiento',
                    'leido' => 0,
                    'fecha_creacion' => now(),
                ]);

            });

            return redirect()
                ->route('finalizacion.trabajos')
                ->with(
                    'success',
                    "Mantenimiento #{$id} finalizado correctamente. " .
                    "Se notifico al solicitante."
                );

        } catch (ValidationException $e) {
            throw $e;

        } catch (HttpExceptionInterface $e) {
            throw $e;

        } catch (Throwable $e) {
            Log::error('Error al finalizar mantenimiento', [
                'solicitud_id' => $id,
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'estado' =>
                    'No se pudo finalizar el mantenimiento.',
            ]);
        }
    }

    public function historial(Request $request)
    {
        $usuario = $request->user();
        $rol = $this->rol($usuario);

        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->join(
                'usuarios as u',
                's.solicitante_id',
                '=',
                'u.id'
            )
            ->leftJoin(
                'usuarios as t',
                's.tecnico_asignado_id',
                '=',
                't.id'
            )
            ->where('s.estado', 'Terminado')
            ->select(
                's.id',
                's.ubicacion',
                's.problema_descripcion',
                's.fecha_solicitud',
                's.fecha_actualizacion',
                'u.nombre as solicitante_nombre',
                'u.apellido as solicitante_apellido',
                't.nombre as tecnico_nombre',
                't.apellido as tecnico_apellido'
            );

        if ($rol === 'Tecnico') {

            $consulta->where(
                's.tecnico_asignado_id',
                $usuario->id
            );

        } elseif ($rol === 'Responsable de Area') {

            abort_if(
                $usuario->area_id === null,
                403
            );

            $consulta->where(
                's.area_id',
                $usuario->area_id
            );

        } elseif ($rol === 'Alumno') {

            $consulta->where(
                's.solicitante_id',
                $usuario->id
            );

        } elseif (!in_array($rol, [
            'Super Admin',
            'Responsable de Gestion',
        ], true)) {

            abort(403);
        }

        $historial = $consulta
            ->orderByDesc('s.fecha_actualizacion')
            ->paginate(10);

        return view('finalizacion.historial', [
            'historial' => $historial,
        ]);
    }
}
