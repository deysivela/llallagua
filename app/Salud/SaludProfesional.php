<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludProfesional extends Model
{
    protected $table = 'salud_profesionales';

    protected $fillable = [
        'establecimiento_id', 'servicio_id', 'nombre',
        'especialidad', 'fecha', 'franja', 'franja_texto',
    ];

    protected $casts = ['fecha' => 'date'];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }

    public function servicio()
    {
        return $this->belongsTo(SaludServicio::class, 'servicio_id');
    }
}
