<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EvidenciaMantenimientoController extends Controller
{
    private function rol($usuario): string
    {
        return DB::table('roles')
            ->where('id', $usuario->rol_id)
            ->value('nombre_rol') ?? '';
    }

    private function solicitudVisible($usuario, int $id)
    {
        $solicitud = DB::table('solicitudes_mantenimiento')
            ->where('id', $id)
            ->first();

        abort_unless($solicitud, 404);

        $rol = $this->rol($usuario);

        $permitido = match ($rol) {
            'Super Admin', 'Responsable de Gestion' => true,

            'Responsable de Area' =>
                $usuario->area_id !== null &&
                (int) $usuario->area_id === (int) $solicitud->area_id,

            'Tecnico' =>
                $solicitud->tecnico_asignado_id !== null &&
                (int) $solicitud->tecnico_asignado_id ===
                (int) $usuario->id,

            'Alumno' =>
                (int) $solicitud->solicitante_id ===
                (int) $usuario->id,

            default => false,
        };

        abort_unless($permitido, 403);

        return $solicitud;
    }

    private function puedeSubir($usuario, $solicitud): bool
    {
        return $this->rol($usuario) === 'Tecnico'
            && $solicitud->tecnico_asignado_id !== null
            && (int) $solicitud->tecnico_asignado_id ===
               (int) $usuario->id
            && $solicitud->estado === 'En Proceso';
    }

    public function index(Request $request)
    {
        $usuario = $request->user();
        $rol = $this->rol($usuario);

        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->leftJoin(
                'usuarios as t',
                's.tecnico_asignado_id',
                '=',
                't.id'
            )
            ->select(
                's.id',
                's.ubicacion',
                's.problema_descripcion',
                's.estado',
                's.tecnico_asignado_id',
                't.nombre as tecnico_nombre',
                't.apellido as tecnico_apellido'
            );

        if ($rol === 'Tecnico') {
            $consulta->where(
                's.tecnico_asignado_id',
                $usuario->id
            );
        } elseif ($rol === 'Responsable de Area') {
            abort_if($usuario->area_id === null, 403);

            $consulta->where('s.area_id', $usuario->area_id);
        } elseif ($rol === 'Alumno') {
            $consulta->where('s.solicitante_id', $usuario->id);
        } elseif (!in_array($rol, [
            'Super Admin',
            'Responsable de Gestion',
        ], true)) {
            abort(403);
        }

        $solicitudes = $consulta
            ->orderByDesc('s.id')
            ->paginate(10);

        return view('evidencias.index', [
            'solicitudes' => $solicitudes,
            'rol' => $rol,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $usuario = $request->user();

        $solicitud = $this->solicitudVisible(
            $usuario,
            $id
        );

        $evidencias = DB::table('evidencias_mantenimiento')
            ->where('solicitud_id', $id)
            ->orderByDesc('id')
            ->get();

        return view('evidencias.detalle', [
            'solicitud' => $solicitud,
            'evidencias' => $evidencias,
            'puedeSubir' => $this->puedeSubir(
                $usuario,
                $solicitud
            ),
        ]);
    }

    public function store(Request $request, int $id)
    {
        $usuario = $request->user();

        $solicitud = $this->solicitudVisible(
            $usuario,
            $id
        );

        abort_unless(
            $this->puedeSubir($usuario, $solicitud),
            403
        );

        $datos = $request->validate([
            'fotografia' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'descripcion_evidencia' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        $ruta = null;

        try {
            $ruta = $request
                ->file('fotografia')
                ->store(
                    'evidencias/solicitud-' . $id,
                    'local'
                );

            if (!$ruta) {
                throw new \RuntimeException(
                    'No se pudo guardar la fotografia.'
                );
            }

            DB::transaction(function () use (
                $id,
                $usuario,
                $datos,
                $ruta
            ) {
                $actual = DB::table(
                    'solicitudes_mantenimiento'
                )
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$actual ||
                    $actual->tecnico_asignado_id === null ||
                    (int) $actual->tecnico_asignado_id !==
                    (int) $usuario->id ||
                    $actual->estado !== 'En Proceso'
                ) {
                    throw ValidationException::withMessages([
                        'fotografia' =>
                            'Esta solicitud ya no permite subir evidencias.',
                    ]);
                }

                $evidenciaId = DB::table(
                    'evidencias_mantenimiento'
                )->insertGetId([
                    'solicitud_id' => $id,
                    'ruta_archivo' => $ruta,
                    'descripcion_evidencia' =>
                        $datos['descripcion_evidencia'] ?? null,
                ]);

                DB::table('bitacora_acciones')->insert([
                    'usuario_id' => $usuario->id,
                    'accion' => 'Carga de evidencia de mantenimiento',
                    'detalles' => json_encode([
                        'solicitud_id' => $id,
                        'evidencia_id' => $evidenciaId,
                        'ruta_archivo' => $ruta,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            });

            return redirect()
                ->route('evidencias.show', $id)
                ->with(
                    'success',
                    'Fotografia guardada correctamente.'
                );

        } catch (ValidationException $e) {
            if ($ruta) {
                Storage::disk('local')->delete($ruta);
            }

            throw $e;

        } catch (Throwable $e) {
            if ($ruta) {
                Storage::disk('local')->delete($ruta);
            }

            Log::error(
                'Error al subir evidencia UPTEX',
                [
                    'solicitud_id' => $id,
                    'usuario_id' => $usuario->id,
                    'error' => $e->getMessage(),
                ]
            );

            return back()
                ->withInput()
                ->withErrors([
                    'fotografia' =>
                        'No se pudo guardar la fotografia.',
                ]);
        }
    }

    public function imagen(Request $request, int $id)
    {
        $evidencia = DB::table(
            'evidencias_mantenimiento'
        )
            ->where('id', $id)
            ->first();

        abort_unless($evidencia, 404);

        $this->solicitudVisible(
            $request->user(),
            (int) $evidencia->solicitud_id
        );

        $ruta = $evidencia->ruta_archivo;

        $prefijo = 'evidencias/solicitud-' .
            $evidencia->solicitud_id . '/';

        abort_unless(
            str_starts_with($ruta, $prefijo) &&
            !str_contains($ruta, '..') &&
            !str_contains($ruta, '\\'),
            404
        );

        $disco = Storage::disk('local');

        abort_unless(
            $disco->exists($ruta),
            404
        );

        $tipo = $disco->mimeType($ruta);

        abort_unless(
            in_array($tipo, [
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true),
            404
        );

        return response(
            $disco->get($ruta),
            200,
            [
                'Content-Type' => $tipo,
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
