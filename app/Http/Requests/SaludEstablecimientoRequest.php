<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaludEstablecimientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre'       => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|alpha_dash',
            'tipo'         => 'required|in:hospital,centro_salud,rehabilitacion',
            'nivel'        => 'nullable|string|max:255',
            'zona'         => 'nullable|string|max:255',
            'direccion'    => 'nullable|string|max:255',
            'latitud'      => 'nullable|numeric|between:-90,90',
            'longitud'     => 'nullable|numeric|between:-180,180',
            'responsable'  => 'nullable|string|max:255',
            'descripcion'  => 'nullable|string|max:3000',
            'atiende_24h'  => 'nullable|boolean',
            'activo'       => 'nullable|boolean',
            'foto_fachada_id' => 'nullable|integer|exists:salud_archivos,id',

            'telefonos'          => 'nullable|array',
            'telefonos.*.numero' => 'nullable|string|max:30',
            'telefonos.*.etiqueta' => 'nullable|in:fijo,celular,whatsapp',

            'horarios'                => 'nullable|array',
            'horarios.*.dias'         => 'nullable|array',
            'horarios.*.dias.*'       => 'integer|between:1,7',
            'horarios.*.hora_inicio'  => 'nullable|date_format:H:i',
            'horarios.*.hora_fin'     => 'nullable|date_format:H:i',
            'horarios.*.nota'         => 'nullable|string|max:255',

            'servicios'             => 'nullable|array',
            'servicios.*.nombre'    => 'nullable|string|max:255',
            'servicios.*.categoria' => 'nullable|string|max:40',
            'servicios.*.descripcion' => 'nullable|string|max:4000',

            'secciones'          => 'nullable|array',
            'secciones.*.titulo' => 'nullable|string|max:255',
            'secciones.*.texto'  => 'nullable|string|max:2000',
            'secciones.*.items'  => 'nullable|string|max:4000',

            'cronograma_filas'                => 'nullable|array',
            'cronograma_filas.*.dia_o_fecha'  => 'nullable|string|max:255',
            'cronograma_filas.*.servicio'     => 'nullable|string|max:255',
            'cronograma_filas.*.profesional'  => 'nullable|string|max:255',
            'cronograma_filas.*.horario'      => 'nullable|string|max:255',
            'cronograma_filas.*.observaciones' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required'   => 'El nombre del establecimiento es obligatorio.',
            'tipo.required'     => 'Debes elegir el tipo de establecimiento.',
            'tipo.in'           => 'El tipo de establecimiento no es válido.',
            'slug.alpha_dash'   => 'El identificador solo admite letras, números, guiones y guiones bajos.',
            'latitud.between'   => 'La latitud debe estar entre -90 y 90.',
            'longitud.between'  => 'La longitud debe estar entre -180 y 180.',
            'foto_fachada_id.exists' => 'La fotografía seleccionada no existe.',
            'horarios.*.hora_inicio.date_format' => 'La hora de inicio debe tener el formato HH:MM.',
            'horarios.*.hora_fin.date_format'    => 'La hora de fin debe tener el formato HH:MM.',
        ];
    }
}
