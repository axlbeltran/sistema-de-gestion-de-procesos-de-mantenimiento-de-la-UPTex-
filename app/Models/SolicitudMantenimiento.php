<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudMantenimiento extends Model
{
    protected $table = 'solicitudes_mantenimiento';

    public $timestamps = false;

    protected $fillable = [
        'solicitante_id',
        'area_id',
        'tipo_servicio_id',
        'problema_descripcion',
        'ubicacion',
        'nivel_urgencia',
        'estado',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function solicitante()
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'tipo_servicio_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(Usuario::class, 'tecnico_asignado_id');
    }
}
