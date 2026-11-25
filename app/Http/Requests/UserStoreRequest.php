<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employee_id' => 'nullable|integer|exists:employees,id',
            'statu' => 'required',
            'user' => 'required|string|max:191|unique:users,user',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.integer' => 'El funcionario seleccionado no es válido.',
            'employee_id.exists' => 'El funcionario seleccionado no existe.',
            'statu.required' => 'Elija el estado para el nuevo usuario.',
            'user.required' => 'Ingrese su Usuario.',
            'user.unique' => 'Ese nombre de usuario ya está registrado.',
            'password.required' => 'El campo Contraseña es obligatorio.',
            'password.min' => 'La contraseña debe contener mínimo 6 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'password_confirmation.required' => 'El campo confirmación de contraseña es obligatorio.',
        ];
    }
}
