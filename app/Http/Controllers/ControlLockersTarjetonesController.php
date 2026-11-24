<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ControlLockersTarjetonesController extends Controller
{
    private function verificar(Request $request): void
    {
        abort_unless(
            $request->user() &&
            in_array((int) $request->user()->rol_id, [1, 2], true),
            403
        );
    }

    public function index(Request $request)
    {
        $this->verificar($request);

        $buscar = trim((string) $request->input('buscar', ''));

        $lockers = DB::table('lockers')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('numero_locker', 'like', "%{$buscar}%")
                        ->orWhere('ubicacion', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('numero_locker')
            ->paginate(10, ['*'], 'pagina_lockers')
            ->withQueryString();

        $tarjetones = DB::table('tarjetones')
            ->when($buscar !== '', function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('codigo_tarjeton', 'like', "%{$buscar}%")
                        ->orWhere('tipo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('codigo_tarjeton')
            ->paginate(10, ['*'], 'pagina_tarjetones')
            ->withQueryString();

        $asignaciones = DB::table('asignaciones_lockers_tarjetones as a')
            ->join('usuarios as u', 'a.usuario_id', '=', 'u.id')
            ->leftJoin('lockers as l', 'a.locker_id', '=', 'l.id')
            ->leftJoin('tarjetones as t', 'a.tarjeton_id', '=', 't.id')
            ->select(
                'a.*',
                'u.nombre',
                'u.apellido',
                'l.numero_locker',
                't.codigo_tarjeton'
            )
            ->orderByDesc('a.id')
            ->paginate(10, ['*'], 'pagina_asignaciones')
            ->withQueryString();

        return view('control-recursos.index', compact(
            'lockers',
            'tarjetones',
            'asignaciones'
        ));
    }

    public function crearLocker(Request $request)
    {
        $this->verificar($request);

        return view('control-recursos.crear-locker');
    }

    public function guardarLocker(Request $request)
    {
        $this->verificar($request);

        $datos = $request->validate([
            'numero_locker' => [
                'required',
                'string',
                'max:20',
                Rule::unique('lockers', 'numero_locker'),
            ],
            'ubicacion' => ['required', 'string', 'max:100'],
            'estado' => [
                'required',
                Rule::in(['Disponible', 'Mantenimiento']),
            ],
        ]);

        try {
            DB::transaction(function () use ($datos, $request) {
                $id = DB::table('lockers')->insertGetId([
                    'numero_locker' => trim($datos['numero_locker']),
                    'ubicacion' => trim($datos['ubicacion']),
                    'estado' => $datos['estado'],
                ]);

                $this->bitacora(
                    $request->user()->id,
                    'Registro de locker',
                    ['locker_id' => $id]
                );
            });

            return redirect()
                ->route('control-recursos.index')
                ->with('success', 'Locker registrado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al registrar locker', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'general' => 'No se pudo registrar el locker.',
            ]);
        }
    }

    public function crearTarjeton(Request $request)
    {
        $this->verificar($request);

        return view('control-recursos.crear-tarjeton');
    }

    public function guardarTarjeton(Request $request)
    {
        $this->verificar($request);

        $datos = $request->validate([
            'codigo_tarjeton' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarjetones', 'codigo_tarjeton'),
            ],
            'tipo' => ['required', 'string', 'max:50'],
        ]);

        try {
            DB::transaction(function () use ($datos, $request) {
                $id = DB::table('tarjetones')->insertGetId([
                    'codigo_tarjeton' => trim($datos['codigo_tarjeton']),
                    'tipo' => trim($datos['tipo']),
                ]);

                $this->bitacora(
                    $request->user()->id,
                    'Registro de tarjeton',
                    ['tarjeton_id' => $id]
                );
            });

            return redirect()
                ->route('control-recursos.index')
                ->with('success', 'Tarjeton registrado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al registrar tarjeton', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'general' => 'No se pudo registrar el tarjeton.',
            ]);
        }
    }

    public function crearAsignacion(Request $request)
    {
        $this->verificar($request);

        $usuarios = DB::table('usuarios')
            ->select('id', 'nombre', 'apellido', 'correo')
            ->orderBy('nombre')
            ->get();

        $lockers = DB::table('lockers')
            ->where('estado', 'Disponible')
            ->whereNotIn('id', function ($q) {
                $q->select('locker_id')
                    ->from('asignaciones_lockers_tarjetones')
                    ->where('estado', 'Activo')
                    ->whereNotNull('locker_id');
            })
            ->orderBy('numero_locker')
            ->get();

        $tarjetones = DB::table('tarjetones')
            ->whereNotIn('id', function ($q) {
                $q->select('tarjeton_id')
                    ->from('asignaciones_lockers_tarjetones')
                    ->where('estado', 'Activo')
                    ->whereNotNull('tarjeton_id');
            })
            ->orderBy('codigo_tarjeton')
            ->get();

        return view('control-recursos.crear-asignacion', compact(
            'usuarios',
            'lockers',
            'tarjetones'
        ));
    }

    public function guardarAsignacion(Request $request)
    {
        $this->verificar($request);

        $datos = $request->validate([
            'usuario_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id'),
            ],
            'locker_id' => [
                'nullable',
                'integer',
                Rule::exists('lockers', 'id'),
            ],
            'tarjeton_id' => [
                'nullable',
                'integer',
                Rule::exists('tarjetones', 'id'),
            ],
            'es_presencial' => ['required', 'boolean'],
            'pago_sellado_tesoreria' => ['required', 'boolean'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $lockerId = $datos['locker_id'] ?? null;
        $tarjetonId = $datos['tarjeton_id'] ?? null;

        if (!$lockerId && !$tarjetonId) {
            throw ValidationException::withMessages([
                'locker_id' =>
                    'Debes seleccionar un locker, un tarjeton o ambos.',
            ]);
        }

        if ((int) $datos['pago_sellado_tesoreria'] !== 1) {
            throw ValidationException::withMessages([
                'pago_sellado_tesoreria' =>
                    'Se requiere el pago sellado por Tesoreria.',
            ]);
        }

        try {
            DB::transaction(function () use (
                $request,
                $datos,
                $lockerId,
                $tarjetonId
            ) {
                if ($lockerId) {
                    $locker = DB::table('lockers')
                        ->where('id', $lockerId)
                        ->lockForUpdate()
                        ->first();

                    if (!$locker || $locker->estado !== 'Disponible') {
                        throw ValidationException::withMessages([
                            'locker_id' => 'El locker no esta disponible.',
                        ]);
                    }

                    $ocupado = DB::table('asignaciones_lockers_tarjetones')
                        ->where('locker_id', $lockerId)
                        ->where('estado', 'Activo')
                        ->exists();

                    if ($ocupado) {
                        throw ValidationException::withMessages([
                            'locker_id' => 'El locker ya tiene una asignacion activa.',
                        ]);
                    }
                }

                if ($tarjetonId) {
                    $tarjeton = DB::table('tarjetones')
                        ->where('id', $tarjetonId)
                        ->lockForUpdate()
                        ->first();

                    abort_unless($tarjeton, 404);

                    $ocupado = DB::table('asignaciones_lockers_tarjetones')
                        ->where('tarjeton_id', $tarjetonId)
                        ->where('estado', 'Activo')
                        ->exists();

                    if ($ocupado) {
                        throw ValidationException::withMessages([
                            'tarjeton_id' =>
                                'El tarjeton ya tiene una asignacion activa.',
                        ]);
                    }
                }

                $id = DB::table('asignaciones_lockers_tarjetones')
                    ->insertGetId([
                        'usuario_id' => $datos['usuario_id'],
                        'locker_id' => $lockerId,
                        'tarjeton_id' => $tarjetonId,
                        'es_presencial' => $datos['es_presencial'],
                        'pago_sellado_tesoreria' =>
                            $datos['pago_sellado_tesoreria'],
                        'fecha_vencimiento' => $datos['fecha_vencimiento'],
                        'estado' => 'Activo',
                    ]);

                if ($lockerId) {
                    DB::table('lockers')
                        ->where('id', $lockerId)
                        ->update(['estado' => 'Asignado']);
                }

                $this->bitacora(
                    $request->user()->id,
                    'Asignacion de locker o tarjeton',
                    [
                        'asignacion_id' => $id,
                        'usuario_id' => $datos['usuario_id'],
                        'locker_id' => $lockerId,
                        'tarjeton_id' => $tarjetonId,
                    ]
                );
            });

            return redirect()
                ->route('control-recursos.index')
                ->with('success', 'Asignacion registrada correctamente.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (Throwable $e) {
            Log::error('Error al registrar asignacion', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors([
                'general' => 'No se pudo registrar la asignacion.',
            ]);
        }
    }

    public function cancelarAsignacion(Request $request, int $id)
    {
        $this->verificar($request);

        try {
            DB::transaction(function () use ($request, $id) {
                $asignacion = DB::table('asignaciones_lockers_tarjetones')
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->first();

                abort_unless($asignacion, 404);

                if ($asignacion->estado !== 'Activo') {
                    throw ValidationException::withMessages([
                        'general' =>
                            'Solo se pueden cancelar asignaciones activas.',
                    ]);
                }

                DB::table('asignaciones_lockers_tarjetones')
                    ->where('id', $id)
                    ->update(['estado' => 'Cancelado']);

                if ($asignacion->locker_id !== null) {
                    $otras = DB::table('asignaciones_lockers_tarjetones')
                        ->where('locker_id', $asignacion->locker_id)
                        ->where('estado', 'Activo')
                        ->exists();

                    if (!$otras) {
                        DB::table('lockers')
                            ->where('id', $asignacion->locker_id)
                            ->where('estado', 'Asignado')
                            ->update(['estado' => 'Disponible']);
                    }
                }

                $this->bitacora(
                    $request->user()->id,
                    'Cancelacion de asignacion',
                    ['asignacion_id' => $id]
                );
            });

            return redirect()
                ->route('control-recursos.index')
                ->with('success', 'Asignacion cancelada correctamente.');

        } catch (ValidationException $e) {
            throw $e;

        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
        ) {
            throw $e;

        } catch (Throwable $e) {
            Log::error('Error al cancelar asignacion', [
                'asignacion_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'No se pudo cancelar la asignacion.',
            ]);
        }
    }

    private function bitacora(int $usuarioId, string $accion, array $datos): void
    {
        DB::table('bitacora_acciones')->insert([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'detalles' => json_encode(
                $datos,
                JSON_UNESCAPED_UNICODE
            ),
        ]);
    }
}
