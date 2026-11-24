<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludCronogramaMensual extends Model
{
    protected $table = 'salud_cronogramas_mensuales';

    const TIPOS = [
        'cronograma_atencion'   => 'Cronograma semanal/mensual de atención por especialidad',
        'calendario_vacunacion' => 'Calendario de vacunación',
        'campana_salud'         => 'Campañas de salud (vacunación, odontología, Papanicolaou, etc.)',
        'feria_brigada'         => 'Ferias de salud y brigadas en comunidades',
        'charla_educativa'      => 'Charlas educativas (planificación familiar, prevención de enfermedades)',
    ];

    const TIPOS_CORTOS = [
        'cronograma_atencion'   => 'Cronograma de atención',
        'calendario_vacunacion' => 'Calendario de vacunación',
        'campana_salud'         => 'Campaña de salud',
        'feria_brigada'         => 'Feria y brigada en comunidad',
        'charla_educativa'      => 'Charla educativa',
    ];

    protected $fillable = [
        'establecimiento_id', 'servicio_id', 'tipo', 'mes', 'fecha_fin',
        'archivo_id', 'titulo', 'nota', 'publicado', 'subido_por', 'eliminado',
    ];

    protected $casts = [
        'publicado' => 'boolean',
        'eliminado' => 'boolean',
        'mes'       => 'date',
        'fecha_fin' => 'date',
    ];

    public function getTipoTextoAttribute(): string
    {
        $tipo = $this->tipo ?: 'cronograma_atencion';
        return self::TIPOS[$tipo] ?? self::TIPOS['cronograma_atencion'];
    }

    public function getTipoCortoAttribute(): string
    {
        $tipo = $this->tipo ?: 'cronograma_atencion';
        return self::TIPOS_CORTOS[$tipo] ?? self::TIPOS_CORTOS['cronograma_atencion'];
    }

    public function establecimiento()
    {
        return $this->belongsTo(SaludEstablecimiento::class, 'establecimiento_id');
    }

    public function servicio()
    {
        return $this->belongsTo(SaludServicio::class, 'servicio_id');
    }

    public function archivo()
    {
        return $this->belongsTo(SaludArchivo::class, 'archivo_id');
    }

    public function scopePublicados($query)
    {
        return $query->where('publicado', true)->where('eliminado', false);
    }

    public function scopeVigentes($query)
    {
        $hoy = \Carbon\Carbon::now('America/La_Paz')->toDateString();

        return $query->where('publicado', true)
            ->where('eliminado', false)
            ->where(function ($q) use ($hoy) {
                $q->whereNull('fecha_fin')
                  ->orWhereDate('fecha_fin', '>=', $hoy);
            });
    }

    public function scopeCampanasPortada($query)
    {
        return $query->vigentes()
            ->whereIn('tipo', [
                'campana_salud',
                'feria_brigada',
                'calendario_vacunacion',
                'charla_educativa',
            ]);
    }

    public function scopeActivos($query)
    {
        return $query->where('eliminado', false);
    }
}
