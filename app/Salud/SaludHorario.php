<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludHorario extends Model
{
    protected $table = 'salud_horarios';

    protected $fillable = ['establecimiento_id', 'dias', 'hora_inicio', 'hora_fin', 'nota'];

    protected $casts = ['dias' => 'array'];

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }

    /**
     * Nombre legible de los días.
     */
    public function diasTexto(): string
    {
        $nombres = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
        $dias = $this->dias ?? [];
        sort($dias);
        return implode(', ', array_map(function ($d) use ($nombres) { return $nombres[$d] ?? $d; }, $dias));
    }
}
