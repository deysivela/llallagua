<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludSeccionDetalle extends Model
{
    protected $table = 'salud_secciones_detalle';

    protected $fillable = ['establecimiento_id', 'titulo', 'texto', 'items', 'orden'];

    protected $casts = ['items' => 'array'];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }
}
