<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class SaludArchivo extends Model
{
    public $timestamps = false;

    protected $table = 'salud_archivos';

    protected $fillable = [
        'clave', 'clave_thumb', 'clave_opt',
        'nombre_original', 'mime', 'tamano',
        'ancho', 'alto', 'subido_por',
    ];

    public $dates = ['created_at'];

    /**
     * El disco "public" del proyecto apunta directamente a public/,
     * por eso las claves se sirven con asset() y no con /storage.
     */
    public function urlPublica(): string
    {
        return asset(ltrim($this->clave, '/'));
    }

    public function urlThumb(): ?string
    {
        return $this->clave_thumb
            ? asset(ltrim($this->clave_thumb, '/'))
            : null;
    }

    public function urlOpt(): ?string
    {
        return $this->clave_opt
            ? asset(ltrim($this->clave_opt, '/'))
            : $this->urlPublica();
    }

    public function esPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function esImagen(): bool
    {
        return in_array($this->mime, [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        ], true);
    }

    public function urlPreferida(): string
    {
        return $this->esImagen() ? ($this->urlOpt() ?: $this->urlPublica()) : $this->urlPublica();
    }
}
