<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludServicio extends Model
{
    protected $table = 'salud_servicios';

    protected $fillable = ['establecimiento_id', 'nombre', 'categoria', 'descripcion', 'orden'];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }

    public function cronogramas()
    {
        return $this->hasMany(SaludCronogramaMensual::class, 'servicio_id');
    }
}
