<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    protected $table = 'tipos_servicio';

    public $timestamps = false;

    protected $fillable = [
        'nombre_servicio',
    ];
}
