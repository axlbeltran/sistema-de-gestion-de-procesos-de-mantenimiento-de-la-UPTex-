<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevantamientoTecnico extends Model
{
    protected $table = 'levantamientos_tecnicos';

    public $timestamps = false;

    protected $fillable = [
        'solicitud_id',
        'elaborado_por',
        'origen_mantenimiento',
        'observaciones_origen',
        'tipo_mantenimiento',
        'observaciones_tipo',
        'cuenta_con_insumos',
        'observaciones_insumos',
        'es_urgente',
        'observaciones_urgencia',
        'tiempo_aproximado',
        'observaciones_tiempo',
        'representa_riesgo',
        'observaciones_riesgo',
        'diagnostico_tecnico',
        'materiales_utilizar',
        'estado',
        'fecha_elaboracion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'cuenta_con_insumos' => 'boolean',
        'es_urgente' => 'boolean',
        'representa_riesgo' => 'boolean',
        'fecha_elaboracion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(
            SolicitudMantenimiento::class,
            'solicitud_id'
        );
    }

    public function elaborador()
    {
        return $this->belongsTo(
            Usuario::class,
            'elaborado_por'
        );
    }
}
