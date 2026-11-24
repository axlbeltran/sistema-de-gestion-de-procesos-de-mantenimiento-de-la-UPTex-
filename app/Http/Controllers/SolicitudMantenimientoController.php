<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\SolicitudMantenimiento;
use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SolicitudMantenimientoController extends Controller
{
    private function esAdministrador(Request $request): bool
    {
        return (int) $request->user()->rol_id === 1;
    }

    private function consultaAutorizada(Request $request)
    {
        $usuario = $request->user();

        $consulta = SolicitudMantenimiento::query();

        if ($this->esAdministrador($request)) {
            return $consulta;
        }

        return $consulta->where(function ($q) use ($usuario) {
            $q->where('solicitante_id', $usuario->id);

            if ((int) $usuario->rol_id === 2) {
                $q->orWhereNotNull('id');
            }

            if ((int) $usuario->rol_id === 3 && $usuario->area_id) {
                $q->orWhere('area_id', $usuario->area_id);
            }

            if ((int) $usuario->rol_id === 4) {
                $q->orWhere('tecnico_asignado_id', $usuario->id);
            }
        });
    }

    public function index(Request $request)
    {
        $consulta = $this->consultaAutorizada($request)
            ->with(['solicitante', 'area', 'tipoServicio']);

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');

            $consulta->where(function ($q) use ($buscar) {
                $q->where('problema_descripcion', 'like', "%{$buscar}%")
                  ->orWhere('ubicacion', 'like', "%{$buscar}%");
            });
        }

        if (in_array($request->input('estado'), [
            'Pendiente', 'En Proceso', 'Terminado'
        ], true)) {
            $consulta->where('estado', $request->input('estado'));
        }

        $solicitudes = $consulta
            ->orderByDesc('fecha_solicitud')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        return view('solicitudes.formulario', [
            'solicitud' => new SolicitudMantenimiento(),
            'areas' => Area::orderBy('nombre_area')->get(),
            'tipos' => TipoServicio::orderBy('nombre_servicio')->get(),
        ]);
    }

    public function store(Request $request)
    {
        return app(RegistroSolicitudController::class)
            ->store($request);
    }

    public function edit(Request $request, SolicitudMantenimiento $solicitud)
    {
        $this->autorizarEdicion($request, $solicitud);

        return view('solicitudes.formulario', [
            'solicitud' => $solicitud,
            'areas' => Area::orderBy('nombre_area')->get(),
            'tipos' => TipoServicio::orderBy('nombre_servicio')->get(),
        ]);
    }

    public function update(
        Request $request,
        SolicitudMantenimiento $solicitud
    ) {
        $this->autorizarEdicion($request, $solicitud);

        $datos = $request->validate([
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'tipo_servicio_id' => [
                'required', 'integer', 'exists:tipos_servicio,id'
            ],
            'problema_descripcion' => [
                'required', 'string', 'min:10', 'max:5000'
            ],
            'ubicacion' => ['required', 'string', 'max:150'],
            'nivel_urgencia' => [
                'required', Rule::in(['Baja', 'Media', 'Alta'])
            ],
        ]);

        DB::transaction(function () use ($request, $solicitud, $datos) {

            $solicitudActual = SolicitudMantenimiento::query()
                ->whereKey($solicitud->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->autorizarEdicion($request, $solicitudActual);

            $areaAnterior = $solicitudActual->area_id;

            $solicitudActual->update($datos);

            if ((int) $areaAnterior !== (int) $datos['area_id']) {

                DB::table('solicitudes_mantenimiento')
                    ->where('id', $solicitudActual->id)
                    ->update([
                        'responsable_area_id' => null,
                    ]);
            }

            DB::table('bitacora_acciones')->insert([
                'usuario_id' => $request->user()->id,
                'accion' => 'Edicion de solicitud pendiente de autorizacion',
                'detalles' => json_encode([
                    'solicitud_id' => $solicitudActual->id,
                    'area_anterior' => $areaAnterior,
                    'area_nueva' => $datos['area_id'],
                ], JSON_UNESCAPED_UNICODE),
                'fecha_accion' => now(),
            ]);

            $responsables = DB::table('usuarios')
                ->where('rol_id', 3)
                ->where('area_id', $datos['area_id'])
                ->where('id', '!=', $request->user()->id)
                ->pluck('id');

            if ($responsables->isEmpty()) {

                $responsables = DB::table('usuarios')
                    ->where('rol_id', 1)
                    ->where('id', '!=', $request->user()->id)
                    ->pluck('id');
            }

            foreach ($responsables as $responsableId) {

                DB::table('notificaciones')->insert([
                    'usuario_id' => $responsableId,
                    'mensaje' =>
                        "La solicitud #{$solicitudActual->id} " .
                        "fue actualizada y requiere revision.",
                    'tipo' => 'Mantenimiento',
                    'leido' => 0,
                    'fecha_creacion' => now(),
                ]);
            }
        });

        return redirect()
            ->route('mis-solicitudes')
            ->with('success', 'Solicitud actualizada correctamente.');
    }

    private function autorizarEdicion(
        Request $request,
        SolicitudMantenimiento $solicitud
    ): void {
        abort_unless(
            (int) $solicitud->solicitante_id ===
                (int) $request->user()->id
            && $solicitud->estado === 'Pendiente'
            && $solicitud->tecnico_asignado_id === null,
            403,
            'Solo puedes editar tus solicitudes pendientes y sin asignar.'
        );

        $tieneAutorizacion = DB::table('autorizaciones_mantenimiento')
            ->where('solicitud_id', $solicitud->id)
            ->where('etapa', 'Autorizacion Inicial')
            ->exists();

        abort_if(
            $tieneAutorizacion,
            403,
            'No puedes modificar una solicitud que ya cuenta con una decision de autorizacion.'
        );
    }
}
