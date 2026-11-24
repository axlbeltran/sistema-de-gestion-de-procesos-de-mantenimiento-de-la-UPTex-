<?php

namespace App\Http\Controllers;

use App\Models\LevantamientoTecnico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LevantamientoTecnicoController extends Controller
{
    private function puedeGestionar($usuario): bool
    {
        return in_array(
            (int) $usuario->rol_id,
            [1, 2],
            true
        );
    }

    private function verificarGestion($usuario): void
    {
        abort_unless(
            $this->puedeGestionar($usuario),
            403
        );
    }

    private function consultaSolicitudes($usuario)
    {
        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->join('usuarios as u', 's.solicitante_id', '=', 'u.id')
            ->join('areas as a', 's.area_id', '=', 'a.id')
            ->join('tipos_servicio as ts', 's.tipo_servicio_id', '=', 'ts.id')
            ->leftJoin(
                'levantamientos_tecnicos as lt',
                's.id',
                '=',
                'lt.solicitud_id'
            )
            ->select(
                's.*',
                'u.nombre as solicitante_nombre',
                'u.apellido as solicitante_apellido',
                'a.nombre_area',
                'ts.nombre_servicio',
                'lt.id as levantamiento_id',
                'lt.estado as estado_levantamiento'
            )
            ->whereExists(function ($q) {
                $q->selectRaw('1')
                    ->from('autorizaciones_mantenimiento as am')
                    ->whereColumn('am.solicitud_id', 's.id')
                    ->where('am.etapa', 'Autorizacion Inicial')
                    ->where('am.decision', 'Aprobada');
            });

        if (!$this->puedeGestionar($usuario)) {

            $consulta->whereNotNull('lt.id');

            $consulta->where(function ($q) use ($usuario) {

                $q->where('s.solicitante_id', $usuario->id);

                if ((int) $usuario->rol_id === 3) {
                    $q->orWhere('s.area_id', $usuario->area_id);
                }

                if ((int) $usuario->rol_id === 4) {
                    $q->orWhere(
                        's.tecnico_asignado_id',
                        $usuario->id
                    );
                }
            });
        }

        return $consulta;
    }

    public function index(Request $request)
    {
        $usuario = $request->user();

        $consulta = $this->consultaSolicitudes($usuario);

        if ($this->puedeGestionar($usuario)) {

            $consulta->where(function ($q) {

                $q->where(function ($pendientes) {

                    $pendientes
                        ->where('s.estado', 'Pendiente')
                        ->whereNull('s.tecnico_asignado_id');

                })->orWhereNotNull('lt.id');
            });
        }

        if ($request->filled('buscar')) {

            $buscar = trim($request->input('buscar'));

            $consulta->where(function ($q) use ($buscar) {

                $q->where('s.id', 'like', "%{$buscar}%")
                    ->orWhere('s.ubicacion', 'like', "%{$buscar}%")
                    ->orWhere('a.nombre_area', 'like', "%{$buscar}%")
                    ->orWhere('s.problema_descripcion', 'like', "%{$buscar}%");
            });
        }

        if (in_array($request->input('estado'), [
            'Borrador',
            'Pendiente de Revision',
            'Aprobado',
            'Rechazado',
        ], true)) {

            $consulta->where(
                'lt.estado',
                $request->input('estado')
            );
        }

        $solicitudes = $consulta
            ->orderByDesc('s.id')
            ->paginate(10)
            ->withQueryString();

        $puedeGestionar = $this->puedeGestionar($usuario);

        return view('levantamientos.index', compact(
            'solicitudes',
            'puedeGestionar'
        ));
    }

    private function verificarSolicitudDisponible(int $id)
    {
        $solicitud = DB::table('solicitudes_mantenimiento')
            ->where('id', $id)
            ->lockForUpdate()
            ->first();

        abort_unless($solicitud, 404);

        if (
            $solicitud->estado !== 'Pendiente' ||
            $solicitud->tecnico_asignado_id !== null
        ) {
            throw ValidationException::withMessages([
                'solicitud' =>
                    'La solicitud ya fue asignada o procesada.',
            ]);
        }

        $aprobada = DB::table('autorizaciones_mantenimiento')
            ->where('solicitud_id', $id)
            ->where('etapa', 'Autorizacion Inicial')
            ->where('decision', 'Aprobada')
            ->exists();

        if (!$aprobada) {
            throw ValidationException::withMessages([
                'solicitud' =>
                    'La solicitud requiere autorizacion inicial aprobada.',
            ]);
        }

        return $solicitud;
    }

    private function validarDatos(Request $request): array
    {
        return $request->validate([
            'origen_mantenimiento' => [
                'required',
                Rule::in(['Interno', 'Externo']),
            ],
            'observaciones_origen' => [
                'nullable', 'string', 'max:3000'
            ],
            'tipo_mantenimiento' => [
                'required',
                Rule::in(['Preventivo', 'Correctivo']),
            ],
            'observaciones_tipo' => [
                'nullable', 'string', 'max:3000'
            ],
            'cuenta_con_insumos' => [
                'required', 'boolean'
            ],
            'observaciones_insumos' => [
                'nullable', 'string', 'max:3000'
            ],
            'es_urgente' => [
                'required', 'boolean'
            ],
            'observaciones_urgencia' => [
                'nullable', 'string', 'max:3000'
            ],
            'tiempo_aproximado' => [
                'required',
                Rule::in([
                    '1 a 4 dias',
                    '5 a 10 dias'
                ]),
            ],
            'observaciones_tiempo' => [
                'nullable', 'string', 'max:3000'
            ],
            'representa_riesgo' => [
                'required', 'boolean'
            ],
            'observaciones_riesgo' => [
                'required_if:representa_riesgo,1',
                'nullable', 'string', 'max:3000'
            ],
            'diagnostico_tecnico' => [
                'required', 'string', 'min:10', 'max:5000'
            ],
            'materiales_utilizar' => [
                'nullable', 'string', 'max:5000'
            ],
        ]);
    }

    private function registrarBitacora(
        int $usuarioId,
        string $accion,
        array $detalles
    ): void {
        DB::table('bitacora_acciones')->insert([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'detalles' => json_encode(
                $detalles,
                JSON_UNESCAPED_UNICODE
            ),
            'fecha_accion' => now(),
        ]);
    }

    public function create(Request $request, int $id)
    {
        $this->verificarGestion($request->user());

        $solicitud = $this->consultaSolicitudes($request->user())
            ->where('s.id', $id)
            ->where('s.estado', 'Pendiente')
            ->whereNull('s.tecnico_asignado_id')
            ->first();

        abort_unless($solicitud, 404);

        if ($solicitud->levantamiento_id !== null) {

            return redirect()->route(
                'levantamientos.show',
                $id
            );
        }

        $levantamiento = new LevantamientoTecnico();

        return view('levantamientos.crear', compact(
            'solicitud',
            'levantamiento'
        ));
    }

    public function store(Request $request, int $id)
    {
        $this->verificarGestion($request->user());

        $datos = $this->validarDatos($request);

        DB::transaction(function () use ($request, $datos, $id) {

            $this->verificarSolicitudDisponible($id);

            $existe = LevantamientoTecnico::query()
                ->where('solicitud_id', $id)
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'solicitud' =>
                        'La solicitud ya cuenta con un levantamiento tecnico.',
                ]);
            }

            $levantamiento = LevantamientoTecnico::create(
                array_merge($datos, [
                    'solicitud_id' => $id,
                    'elaborado_por' => $request->user()->id,
                    'estado' => 'Borrador',
                    'fecha_elaboracion' => now(),
                    'fecha_actualizacion' => now(),
                ])
            );

            $this->registrarBitacora(
                $request->user()->id,
                'Registro de levantamiento tecnico',
                [
                    'solicitud_id' => $id,
                    'levantamiento_id' => $levantamiento->id,
                    'estado' => 'Borrador',
                ]
            );
        });

        return redirect()
            ->route('levantamientos.show', $id)
            ->with(
                'success',
                'Levantamiento tecnico registrado correctamente.'
            );
    }

    public function show(Request $request, int $id)
    {
        $solicitud = $this->consultaSolicitudes($request->user())
            ->where('s.id', $id)
            ->first();

        abort_unless($solicitud, 404);

        $levantamiento = LevantamientoTecnico::query()
            ->with('elaborador')
            ->where('solicitud_id', $id)
            ->firstOrFail();

        $puedeGestionar = $this->puedeGestionar(
            $request->user()
        );

        $puedeRevisar = (int) $request->user()->rol_id === 1
            && (int) $levantamiento->elaborado_por !== (int) $request->user()->id
            && $levantamiento->estado === 'Pendiente de Revision'
            && $solicitud->estado === 'Pendiente'
            && $solicitud->tecnico_asignado_id === null;

        $version = $this->versionRevision($levantamiento);

        $revisiones = DB::table('autorizaciones_mantenimiento as am')
            ->join('usuarios as u', 'am.autorizador_id', '=', 'u.id')
            ->where('am.solicitud_id', $id)
            ->where('am.etapa', 'Visto Bueno Tecnico')
            ->select('am.*', 'u.nombre', 'u.apellido')
            ->orderByDesc('am.id')->get();

        return view('levantamientos.detalle', compact(
            'solicitud',
            'levantamiento',
            'puedeGestionar',
            'puedeRevisar',
            'revisiones',
            'version'
        ));
    }

    public function edit(Request $request, int $id)
    {
        $this->verificarGestion($request->user());

        $solicitud = $this->consultaSolicitudes($request->user())
            ->where('s.id', $id)
            ->where('s.estado', 'Pendiente')
            ->whereNull('s.tecnico_asignado_id')
            ->first();

        abort_unless($solicitud, 404);

        $levantamiento = LevantamientoTecnico::query()
            ->where('solicitud_id', $id)
            ->firstOrFail();

        abort_unless(
            in_array($levantamiento->estado, ['Borrador', 'Rechazado'], true) &&
            (int) $levantamiento->elaborado_por === (int) $request->user()->id,
            403,
            'Solo el elaborador puede corregir borradores o levantamientos rechazados.'
        );

        return view('levantamientos.editar', compact(
            'solicitud',
            'levantamiento'
        ));
    }

    public function update(Request $request, int $id)
    {
        $this->verificarGestion($request->user());

        $datos = $this->validarDatos($request);

        DB::transaction(function () use ($request, $datos, $id) {

            $this->verificarSolicitudDisponible($id);

            $levantamiento = LevantamientoTecnico::query()
                ->where('solicitud_id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                in_array($levantamiento->estado, ['Borrador', 'Rechazado'], true) &&
                (int) $levantamiento->elaborado_por === (int) $request->user()->id,
                403
            );

            $levantamiento->update(
                array_merge($datos, [
                    'estado' => 'Borrador',
                    'fecha_actualizacion' => now(),
                ])
            );

            $this->registrarBitacora(
                $request->user()->id,
                'Actualizacion de levantamiento tecnico',
                [
                    'solicitud_id' => $id,
                    'levantamiento_id' => $levantamiento->id,
                ]
            );
        });

        return redirect()
            ->route('levantamientos.show', $id)
            ->with(
                'success',
                'Levantamiento tecnico actualizado correctamente.'
            );
    }

    public function enviarRevision(Request $request, int $id)
    {
        $this->verificarGestion($request->user());

        DB::transaction(function () use ($request, $id) {

            $this->verificarSolicitudDisponible($id);

            $levantamiento = LevantamientoTecnico::query()
                ->where('solicitud_id', $id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $levantamiento->estado === 'Borrador' &&
                (int) $levantamiento->elaborado_por === (int) $request->user()->id,
                403,
                'El levantamiento ya fue enviado a revision.'
            );

            if (!DB::table('usuarios')->where('rol_id', 1)
                ->where('id', '!=', $levantamiento->elaborado_por)->exists()) {
                throw ValidationException::withMessages([
                    'solicitud' => 'No existe un revisor independiente disponible.',
                ]);
            }

            $levantamiento->update([
                'estado' => 'Pendiente de Revision',
                'fecha_actualizacion' => now(),
            ]);

            $this->registrarBitacora(
                $request->user()->id,
                'Envio de levantamiento tecnico a revision',
                [
                    'solicitud_id' => $id,
                    'levantamiento_id' => $levantamiento->id,
                    'estado' => 'Pendiente de Revision',
                ]
            );

            $revisores = DB::table('usuarios')
                ->where('rol_id', 1)
                ->where('id', '!=', $request->user()->id)
                ->where('id', '!=', $levantamiento->elaborado_por)
                ->pluck('id');

            foreach ($revisores as $revisorId) {

                DB::table('notificaciones')->insert([
                    'usuario_id' => $revisorId,
                    'mensaje' =>
                        "El levantamiento tecnico de la solicitud #{$id} requiere revision.",
                    'tipo' => 'Mantenimiento',
                    'leido' => 0,
                    'fecha_creacion' => now(),
                ]);
            }
        });

        return redirect()
            ->route('levantamientos.show', $id)
            ->with(
                'success',
                'Levantamiento tecnico enviado a revision.'
            );
    }

    public function resolver(Request $request, int $id)
    {
        $usuario = $request->user();
        abort_unless((int) $usuario->rol_id === 1, 403);

        $datos = $request->validate([
            'decision' => ['required', Rule::in(['Aprobada', 'Rechazada'])],
            'observaciones' => ['nullable', 'required_if:decision,Rechazada', 'string', 'max:5000'],
            'version' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($usuario, $datos, $id) {
            $solicitud = $this->verificarSolicitudDisponible($id);
            $levantamiento = LevantamientoTecnico::query()
                ->where('solicitud_id', $id)->lockForUpdate()->firstOrFail();

            abort_unless((int) $levantamiento->elaborado_por !== (int) $usuario->id, 403);
            if ($levantamiento->estado !== 'Pendiente de Revision' ||
                !hash_equals($this->versionRevision($levantamiento), $datos['version'])) {
                throw ValidationException::withMessages([
                    'decision' => 'El diagnostico cambio o ya fue revisado. Recarga la pagina.',
                ]);
            }

            $autorizacionId = DB::table('autorizaciones_mantenimiento')->insertGetId([
                'solicitud_id' => $id,
                'autorizador_id' => $usuario->id,
                'etapa' => 'Visto Bueno Tecnico',
                'decision' => $datos['decision'],
                'observaciones' => $datos['observaciones'] ?? null,
                'fecha_autorizacion' => now(),
            ]);

            $estado = $datos['decision'] === 'Aprobada' ? 'Aprobado' : 'Rechazado';
            $this->registrarBitacora($usuario->id, 'Visto bueno tecnico', [
                'solicitud_id' => $id,
                'levantamiento_id' => $levantamiento->id,
                'autorizacion_id' => $autorizacionId,
                'decision' => $datos['decision'],
                'diagnostico_revisado' => $levantamiento->getAttributes(),
            ]);
            $levantamiento->update(['estado' => $estado, 'fecha_actualizacion' => now()]);

            $destinatarios = [(int) $levantamiento->elaborado_por];
            if ($estado === 'Aprobado') {
                $destinatarios = array_merge($destinatarios, DB::table('usuarios')
                    ->where('rol_id', 2)->pluck('id')->map(fn ($v) => (int) $v)->all());
            }
            foreach (array_unique($destinatarios) as $destinatario) {
                DB::table('notificaciones')->insert([
                    'usuario_id' => $destinatario,
                    'mensaje' => "El levantamiento tecnico de la solicitud #{$id} fue {$estado}.",
                    'tipo' => 'Mantenimiento',
                    'leido' => 0,
                    'fecha_creacion' => now(),
                ]);
            }
        });

        return redirect()->route('levantamientos.show', $id)
            ->with('success', 'Decision tecnica registrada correctamente.');
    }

    private function versionRevision(LevantamientoTecnico $levantamiento): string
    {
        $ultimoEnvio = DB::table('bitacora_acciones')
            ->where('accion', 'Envio de levantamiento tecnico a revision')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(detalles, '$.solicitud_id')) = ?", [(string) $levantamiento->solicitud_id])
            ->max('id');
        return hash('sha256', json_encode([$levantamiento->getAttributes(), $ultimoEnvio]));
    }
}
