<?php

namespace App\Salud;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SaludEstablecimiento extends Model
{
    protected $table = 'salud_establecimientos';

    protected $fillable = [
        'slug', 'nombre', 'tipo', 'nivel', 'zona', 'direccion',
        'latitud', 'longitud', 'responsable', 'descripcion',
        'atiende_24h', 'foto_fachada_id', 'activo',
    ];

    protected $casts = [
        'atiende_24h' => 'boolean',
        'activo'      => 'boolean',
        'latitud'     => 'float',
        'longitud'    => 'float',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function foto()
    {
        return $this->belongsTo(SaludArchivo::class, 'foto_fachada_id');
    }

    public function telefonos()
    {
        return $this->hasMany(SaludTelefono::class, 'establecimiento_id')->orderBy('orden');
    }

    public function horarios()
    {
        return $this->hasMany(SaludHorario::class, 'establecimiento_id');
    }

    public function servicios()
    {
        return $this->hasMany(SaludServicio::class, 'establecimiento_id')->orderBy('orden');
    }

    public function secciones()
    {
        return $this->hasMany(SaludSeccionDetalle::class, 'establecimiento_id')->orderBy('orden');
    }

    public function cronogramasMensuales()
    {
        return $this->hasMany(SaludCronogramaMensual::class, 'establecimiento_id');
    }

    public function cronogramasFilas()
    {
        return $this->hasMany(SaludCronogramaFila::class, 'establecimiento_id')->orderBy('orden');
    }

    public function profesionales()
    {
        return $this->hasMany(SaludProfesional::class, 'establecimiento_id');
    }

    public function responsables()
    {
        return $this->belongsToMany(\App\User::class, 'salud_usuario_establecimiento', 'establecimiento_id', 'user_id');
    }

    // ─── Lógica de negocio ────────────────────────────────────

    /**
     * ¿Está abierto ahora en zona horaria America/La_Paz?
     *
     * Soporta franjas que cruzan la medianoche (hora_fin < hora_inicio).
     */
    public function estaAbierto(): bool
    {
        if ($this->atiende_24h) {
            return true;
        }

        $now = Carbon::now('America/La_Paz');
        // 1=lun, 2=mar, …, 7=dom  (Carbon: dayOfWeekIso)
        $diaSemana = (int) $now->dayOfWeekIso;
        $minutos   = $now->hour * 60 + $now->minute;

        $diaAnterior = $diaSemana === 1 ? 7 : $diaSemana - 1;

        foreach ($this->horarios as $horario) {
            $dias = is_array($horario->dias) ? $horario->dias : json_decode($horario->dias, true);
            $dias = array_map('intval', (array) $dias);

            [$hIni, $mIni] = explode(':', $horario->hora_inicio);
            [$hFin, $mFin] = explode(':', $horario->hora_fin);
            $inicio = (int) $hIni * 60 + (int) $mIni;
            $fin    = (int) $hFin * 60 + (int) $mFin;

            if ($fin === $inicio) {
                // Franja de 24 horas para los días indicados.
                if (in_array($diaSemana, $dias, true)) {
                    return true;
                }
                continue;
            }

            if ($fin > $inicio) {
                // Franja normal dentro del mismo día.
                if (in_array($diaSemana, $dias, true) && $minutos >= $inicio && $minutos < $fin) {
                    return true;
                }
            } else {
                // Franja que cruza medianoche (ej. lunes 20:00–08:00).
                if (in_array($diaSemana, $dias, true) && $minutos >= $inicio) {
                    return true;
                }
                if (in_array($diaAnterior, $dias, true) && $minutos < $fin) {
                    return true;
                }
            }
        }

        return false;
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query
            ->orderByRaw("CASE WHEN tipo = 'hospital' THEN 0 WHEN tipo = 'centro_salud' THEN 1 ELSE 2 END")
            ->orderBy('nombre');
    }

    public function getTipoTextoAttribute(): string
    {
        $tipos = [
            'hospital'       => 'Hospital',
            'centro_salud'   => 'Centro de salud',
            'rehabilitacion' => 'Centro de rehabilitación',
        ];

        return $tipos[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function getNombreCortoAttribute(): string
    {
        return preg_replace('/^Centro de Salud Ambulatorio\b/iu', 'CSA', $this->nombre);
    }

    public function fotoUrl(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        return $this->foto->esImagen()
            ? ($this->foto->urlThumb() ?: $this->foto->urlPublica())
            : $this->foto->urlPublica();
    }

    public function fotoUrlGrande(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        return $this->foto->urlPreferida();
    }

    /**
     * Indicador de completitud para el listado admin.
     * Retorna array de campos pendientes.
     */
    public function camposPendientes(): array
    {
        $pendientes = [];

        if (!$this->foto_fachada_id) {
            $pendientes[] = 'fotografía';
        }
        if ($this->telefonos->isEmpty()) {
            $pendientes[] = 'teléfono';
        }
        if (!$this->latitud || !$this->longitud) {
            $pendientes[] = 'ubicación GPS';
        }

        // Cronograma del mes en curso
        $mesActual = Carbon::now('America/La_Paz')->startOfMonth()->toDateString();
        $tieneCronograma = $this->cronogramasMensuales()
            ->where('mes', $mesActual)
            ->where('publicado', true)
            ->where('eliminado', false)
            ->exists();

        if (!$tieneCronograma) {
            $pendientes[] = 'cronograma del mes';
        }

        return $pendientes;
    }

    /**
     * URL de Google Maps. Si tiene coordenadas usa lat,lng, si no usa el nombre.
     */
    public function urlMapa(): string
    {
        if ($this->latitud && $this->longitud) {
            return "https://www.google.com/maps?q={$this->latitud},{$this->longitud}&t=k";
        }
        return 'https://www.google.com/maps?q=' . urlencode($this->nombre) . '&t=k';
    }
}
