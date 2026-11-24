<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AutorizacionMantenimientoController extends Controller
{
    private function verificarAcceso($usuario): void
    {
        abort_unless(
            in_array((int) $usuario->rol_id, [1, 3], true),
            403
        );
    }

    private function consultaSolicitudes($usuario)
    {
        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->join('usuarios as u', 's.solicitante_id', '=', 'u.id')
            ->join('areas as a', 's.area_id', '=', 'a.id')
            ->join('tipos_servicio as ts', 's.tipo_servicio_id', '=', 'ts.id')
            ->select(
                's.*',
                'u.nombre as solicitante_nombre',
                'u.apellido as solicitante_apellido',
                'a.nombre_area',
                'ts.nombre_servicio'
            )
            ->selectSub(function ($q) {
                $q->from('autorizaciones_mantenimiento as am')
                    ->select('am.decision')
                    ->whereColumn('am.solicitud_id', 's.id')
                    ->where('am.etapa', 'Autorizacion Inicial')
                    ->orderByDesc('am.id')
                    ->limit(1);
            }, 'decision_autorizacion');

        if ((int) $usuario->rol_id === 3) {
            $consulta->where('s.area_id', $usuario->area_id);
        }

        return $consulta;
    }

    public function index(Request $request)
    {
        $usuario = $request->user();

        $this->verificarAcceso($usuario);

        $consulta = $this->consultaSolicitudes($usuario);

        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));

            $consulta->where(function ($q) use ($buscar) {
                $q->where('s.id', 'like', "%{$buscar}%")
                    ->orWhere('s.ubicacion', 'like', "%{$buscar}%")
                    ->orWhere('u.nombre', 'like', "%{$buscar}%")
                    ->orWhere('u.apellido', 'like', "%{$buscar}%");
            });
        }

        $solicitudes = $consulta
            ->where('s.estado', 'Pendiente')
            ->orderByDesc('s.id')
            ->paginate(10)
            ->withQueryString();

        return view('autorizaciones.index', compact(
            'solicitudes',
            'usuario'
        ));
    }

    public function revisar(Request $request, int $id)
    {
        $usuario = $request->user();

        $this->verificarAcceso($usuario);

        $solicitud = $this->consultaSolicitudes($usuario)
            ->where('s.id', $id)
            ->first();

        abort_unless($solicitud, 404);

        $autorizaciones = DB::table('autorizaciones_mantenimiento as am')
            ->join('usuarios as u', 'am.autorizador_id', '=', 'u.id')
            ->where('am.solicitud_id', $id)
            ->select(
                'am.*',
                'u.nombre',
                'u.apellido'
            )
            ->orderByDesc('am.id')
            ->get();

        return view('autorizaciones.revisar', compact(
            'solicitud',
            'autorizaciones',
            'usuario'
        ));
    }

    public function resolver(Request $request, int $id)
    {
        $usuario = $request->user();

        $this->verificarAcceso($usuario);

        $datos = $request->validate([
            'decision' => [
                'required',
                Rule::in(['Aprobada', 'Rechazada']),
            ],
            'observaciones' => [
                Rule::requiredIf(
                    $request->input('decision') === 'Rechazada' ||
                    (int) $usuario->rol_id === 1
                ),
                'nullable',
                'string',
                'max:3000',
            ],
        ]);

        DB::transaction(function () use ($usuario, $datos, $id) {

            $solicitud = DB::table('solicitudes_mantenimiento')
                ->where('id', $id)
                ->lockForUpdate()
                ->first();

            abort_unless($solicitud, 404);

            if ((int) $usuario->rol_id === 3) {
                abort_unless(
                    $usuario->area_id !== null &&
                    (int) $usuario->area_id === (int) $solicitud->area_id,
                    403
                );
            }

            if ((int) $solicitud->solicitante_id === (int) $usuario->id) {
                throw ValidationException::withMessages([
                    'decision' => 'No puedes autorizar tu propia solicitud.',
                ]);
            }

            if (
                $solicitud->estado !== 'Pendiente' ||
                $solicitud->tecnico_asignado_id !== null
            ) {
                throw ValidationException::withMessages([
                    'decision' => 'La solicitud ya se encuentra asignada o procesada.',
                ]);
            }

            $existe = DB::table('autorizaciones_mantenimiento')
                ->where('solicitud_id', $id)
                ->where('etapa', 'Autorizacion Inicial')
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'decision' => 'Esta solicitud ya cuenta con una decision registrada.',
                ]);
            }

            DB::table('autorizaciones_mantenimiento')->insert([
                'solicitud_id' => $id,
                'autorizador_id' => $usuario->id,
                'etapa' => 'Autorizacion Inicial',
                'decision' => $datos['decision'],
                'observaciones' => $datos['observaciones'] ?? null,
                'fecha_autorizacion' => now(),
            ]);

            if (
                $datos['decision'] === 'Aprobada' &&
                (int) $usuario->rol_id === 3
            ) {
                DB::table('solicitudes_mantenimiento')
                    ->where('id', $id)
                    ->update([
                        'responsable_area_id' => $usuario->id,
                    ]);
            }

            DB::table('bitacora_acciones')->insert([
                'usuario_id' => $usuario->id,
                'accion' => 'Autorizacion inicial de mantenimiento',
                'detalles' => json_encode([
                    'solicitud_id' => $id,
                    'decision' => $datos['decision'],
                    'observaciones' => $datos['observaciones'] ?? null,
                    'autorizacion_excepcional' =>
                        (int) $usuario->rol_id === 1,
                ], JSON_UNESCAPED_UNICODE),
                'fecha_accion' => now(),
            ]);

            $mensaje = "Tu solicitud de mantenimiento #{$id} "
                . "ha sido {$datos['decision']}.";

            if (!empty($datos['observaciones'])) {
                $mensaje .= " Observaciones: {$datos['observaciones']}";
            }

            DB::table('notificaciones')->insert([
                'usuario_id' => $solicitud->solicitante_id,
                'mensaje' => $mensaje,
                'tipo' => 'Mantenimiento',
                'leido' => 0,
                'fecha_creacion' => now(),
            ]);

            if ($datos['decision'] === 'Aprobada') {

                $responsables = DB::table('usuarios')
                    ->where('rol_id', 2)
                    ->pluck('id');

                foreach ($responsables as $responsableId) {

                    DB::table('notificaciones')->insert([
                        'usuario_id' => $responsableId,
                        'mensaje' =>
                            "La solicitud #{$id} fue autorizada y esta disponible para su gestion.",
                        'tipo' => 'Mantenimiento',
                        'leido' => 0,
                        'fecha_creacion' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('autorizaciones.index')
            ->with(
                'success',
                "La decision de la solicitud #{$id} se registro correctamente."
            );
    }
}
