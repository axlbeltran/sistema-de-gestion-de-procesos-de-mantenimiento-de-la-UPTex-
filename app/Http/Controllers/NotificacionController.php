<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        $notificaciones = DB::table('notificaciones')
            ->where('usuario_id', $usuario->id)
            ->orderByDesc('fecha_creacion')
            ->orderByDesc('id')
            ->paginate(10);

        $sinLeer = DB::table('notificaciones')
            ->where('usuario_id', $usuario->id)
            ->where('leido', 0)
            ->count();

        return view('notificaciones.index', compact(
            'notificaciones',
            'sinLeer'
        ));
    }

    public function marcarLeida(Request $request, int $id)
    {
        $usuario = $request->user();

        $notificacion = DB::table('notificaciones')
            ->where('id', $id)
            ->where('usuario_id', $usuario->id)
            ->first();

        abort_unless($notificacion, 404);

        DB::table('notificaciones')
            ->where('id', $id)
            ->where('usuario_id', $usuario->id)
            ->update([
                'leido' => 1
            ]);

        return redirect()
            ->route('notificaciones.index')
            ->with('success', 'Notificacion marcada como leida.');
    }

    public function marcarTodas(Request $request)
    {
        DB::table('notificaciones')
            ->where('usuario_id', $request->user()->id)
            ->where(function ($consulta) {
                $consulta->where('leido', 0)
                    ->orWhereNull('leido');
            })
            ->update([
                'leido' => 1
            ]);

        return redirect()
            ->route('notificaciones.index')
            ->with('success', 'Todas las notificaciones fueron marcadas como leidas.');
    }
}
