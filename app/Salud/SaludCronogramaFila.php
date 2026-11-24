<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludCronogramaFila extends Model
{
    protected $table = 'salud_cronogramas_filas';

    protected $fillable = [
        'establecimiento_id', 'dia_o_fecha', 'servicio',
        'profesional', 'horario', 'observaciones', 'orden',
    ];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }
}
