<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'salud_auditoria';

    protected $fillable = ['usuario_id', 'accion', 'entidad', 'entidad_id', 'antes', 'despues'];

    protected $casts = [
        'antes'   => 'array',
        'despues' => 'array',
        'fecha'   => 'datetime',
    ];

    public $dates = ['fecha'];

    public function usuario()
    {
        return $this->belongsTo(\App\User::class, 'usuario_id');
    }

    public function accionTexto(): string
    {
        $acciones = [
            'crear'     => 'Creación',
            'editar'    => 'Edición',
            'eliminar'  => 'Eliminación',
            'publicar'  => 'Publicación',
            'archivo'   => 'Archivo',
        ];

        return $acciones[$this->accion] ?? ucfirst($this->accion);
    }

    /**
     * Registra una entrada de auditoría desde un controlador.
     */
    public static function registrar(
        string $accion,
        string $entidad,
        int $entidadId,
        array $antes = [],
        array $despues = [],
        ?int $usuarioId = null
    ): self {
        return static::create([
            'usuario_id' => $usuarioId ?? optional(auth()->user())->id,
            'accion'     => $accion,
            'entidad'    => $entidad,
            'entidad_id' => $entidadId,
            'antes'      => $antes ?: null,
            'despues'    => $despues ?: null,
        ]);
    }
}
