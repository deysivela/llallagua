<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludTelefono extends Model
{
    protected $table = 'salud_telefonos';

    protected $fillable = ['establecimiento_id', 'numero', 'etiqueta', 'orden'];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }
}
