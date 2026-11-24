<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaludCronogramaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $tipos = implode(',', array_keys(\App\Salud\SaludCronogramaMensual::TIPOS));

        return [
            'establecimiento_id' => 'required|integer|exists:salud_establecimientos,id',
            'servicio_id'        => 'nullable|integer|exists:salud_servicios,id',
            'tipo'               => 'nullable|string|in:' . $tipos,
            'mes'                => 'required|date_format:Y-m',
            'fecha_fin'          => 'nullable|date',
            'titulo'             => 'nullable|string|max:255',
            'nota'               => 'nullable|string|max:1500',
            'publicado'          => 'nullable|boolean',
            'archivo_id'         => 'nullable|integer|exists:salud_archivos,id',
            'archivo'            => 'nullable|file|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'establecimiento_id.required' => 'Debes elegir un establecimiento.',
            'mes.required'                 => 'Debes indicar el mes del cronograma.',
            'mes.date_format'              => 'El mes debe tener el formato AAAA-MM.',
            'archivo_id.required_without'  => 'Debes subir un archivo o seleccionar uno existente.',
            'archivo.max'                  => 'El archivo no debe superar los 10 MB.',
        ];
    }
}
