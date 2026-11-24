<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\SolicitudMantenimiento;
use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class RegistroSolicitudController extends Controller
{
    public function create()
    {
        return view('solicitudes.formulario', [
            'solicitud' => new SolicitudMantenimiento(),
            'areas' => Area::all(),
            'tipos' => TipoServicio::all(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'area_id' => [
                'required',
                'integer',
                'exists:areas,id',
            ],
            'tipo_servicio_id' => [
                'required',
                'integer',
                'exists:tipos_servicio,id',
            ],
            'problema_descripcion' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'ubicacion' => [
                'required',
                'string',
                'max:150',
            ],
            'nivel_urgencia' => [
                'required',
                Rule::in(['Baja', 'Media', 'Alta']),
            ],
        ]);

        try {

            $usuario = $request->user();

            $id = DB::transaction(function () use ($datos, $usuario) {

                $id = DB::table('solicitudes_mantenimiento')
                    ->insertGetId([
                        'solicitante_id' => $usuario->id,
                        'area_id' => $datos['area_id'],
                        'tipo_servicio_id' => $datos['tipo_servicio_id'],
                        'problema_descripcion' => $datos['problema_descripcion'],
                        'ubicacion' => $datos['ubicacion'],
                        'nivel_urgencia' => $datos['nivel_urgencia'],
                        'estado' => 'Pendiente',
                    ]);

                $responsables = DB::table('usuarios')
                    ->where('rol_id', 3)
                    ->where('area_id', $datos['area_id'])
                    ->where('id', '!=', $usuario->id)
                    ->pluck('id');

                if ($responsables->isEmpty()) {

                    $responsables = DB::table('usuarios')
                        ->where('rol_id', 1)
                        ->where('id', '!=', $usuario->id)
                        ->pluck('id');
                }

                foreach ($responsables as $responsableId) {

                    DB::table('notificaciones')->insert([
                        'usuario_id' => $responsableId,
                        'mensaje' =>
                            "La solicitud de mantenimiento #{$id} " .
                            "requiere autorizacion inicial. " .
                            "Ubicacion: {$datos['ubicacion']}.",
                        'tipo' => 'Mantenimiento',
                        'leido' => 0,
                        'fecha_creacion' => now(),
                    ]);
                }

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $usuario->id,
                    'accion' => 'Registro de solicitud de mantenimiento',
                    'detalles' => json_encode([
                        'solicitud_id' => $id,
                        'area_id' => $datos['area_id'],
                        'ubicacion' => $datos['ubicacion'],
                        'estado_autorizacion' => 'Pendiente',
                        'responsables_notificados' => $responsables->all(),
                    ], JSON_UNESCAPED_UNICODE),
                    'fecha_accion' => now(),
                ]);

                return $id;
            });

            return redirect()
                ->route('mis-solicitudes')
                ->with(
                    'success',
                    "Solicitud #{$id} registrada correctamente. Pendiente de autorizacion."
                );

        } catch (Throwable $e) {

            Log::error('Error al registrar solicitud UPTEX', [
                'usuario_id' => $request->user()->id,
                'mensaje' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'registro' =>
                        'No se pudo registrar la solicitud. Revisa el registro de errores.',
                ]);
        }
    }
}
