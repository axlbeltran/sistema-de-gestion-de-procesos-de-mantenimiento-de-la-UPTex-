<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReporteMantenimientoController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            $request->user() &&
            in_array((int) $request->user()->rol_id, [1, 2], true),
            403
        );

        $filtros = $request->validate([
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio'
            ],
            'area_id' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id')
            ],
            'tipo_servicio_id' => [
                'nullable',
                'integer',
                Rule::exists('tipos_servicio', 'id')
            ],
            'estado' => [
                'nullable',
                Rule::in([
                    'Pendiente',
                    'En Proceso',
                    'Terminado'
                ])
            ],
        ]);

        $consulta = DB::table('solicitudes_mantenimiento as s')
            ->leftJoin('areas as a', 's.area_id', '=', 'a.id')
            ->leftJoin('tipos_servicio as ts', 's.tipo_servicio_id', '=', 'ts.id')
            ->leftJoin('usuarios as u', 's.solicitante_id', '=', 'u.id')
            ->leftJoin('usuarios as t', 's.tecnico_asignado_id', '=', 't.id');

        if (!empty($filtros['fecha_inicio'])) {
            $consulta->whereDate(
                's.fecha_solicitud',
                '>=',
                $filtros['fecha_inicio']
            );
        }

        if (!empty($filtros['fecha_fin'])) {
            $consulta->whereDate(
                's.fecha_solicitud',
                '<=',
                $filtros['fecha_fin']
            );
        }

        if (!empty($filtros['area_id'])) {
            $consulta->where('s.area_id', $filtros['area_id']);
        }

        if (!empty($filtros['tipo_servicio_id'])) {
            $consulta->where(
                's.tipo_servicio_id',
                $filtros['tipo_servicio_id']
            );
        }

        if (!empty($filtros['estado'])) {
            $consulta->where('s.estado', $filtros['estado']);
        }

        $total = (clone $consulta)->count();

        $pendientes = (clone $consulta)
            ->where('s.estado', 'Pendiente')
            ->count();

        $enProceso = (clone $consulta)
            ->where('s.estado', 'En Proceso')
            ->count();

        $terminadas = (clone $consulta)
            ->where('s.estado', 'Terminado')
            ->count();

        $porcentajeFinalizacion = $total > 0
            ? round(($terminadas / $total) * 100, 2)
            : 0;

        $porServicio = (clone $consulta)
            ->select('ts.nombre_servicio')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('ts.nombre_servicio')
            ->orderByDesc('total')
            ->get();

        $porArea = (clone $consulta)
            ->select('a.nombre_area')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('a.nombre_area')
            ->orderByDesc('total')
            ->get();

        $porTecnico = (clone $consulta)
            ->whereNotNull('s.tecnico_asignado_id')
            ->select('t.id', 't.nombre', 't.apellido')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "SUM(CASE WHEN s.estado = 'Terminado' THEN 1 ELSE 0 END) as terminadas"
            )
            ->groupBy('t.id', 't.nombre', 't.apellido')
            ->orderByDesc('total')
            ->get();

        $solicitudes = (clone $consulta)
            ->select(
                's.*',
                'a.nombre_area',
                'ts.nombre_servicio',
                'u.nombre as solicitante_nombre',
                'u.apellido as solicitante_apellido',
                't.nombre as tecnico_nombre',
                't.apellido as tecnico_apellido'
            )
            ->orderByDesc('s.fecha_solicitud')
            ->paginate(10)
            ->withQueryString();

        $areas = DB::table('areas')
            ->orderBy('nombre_area')
            ->get();

        $tiposServicio = DB::table('tipos_servicio')
            ->orderBy('nombre_servicio')
            ->get();

        return view('reportes.index', compact(
            'total',
            'pendientes',
            'enProceso',
            'terminadas',
            'porcentajeFinalizacion',
            'porServicio',
            'porArea',
            'porTecnico',
            'solicitudes',
            'areas',
            'tiposServicio'
        ));
    }
}
