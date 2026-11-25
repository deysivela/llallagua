<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $userParam = $this->route('user');
        $userId = is_object($userParam) ? $userParam->id : $userParam;

        return [
            'employee_id' => 'nullable|integer|exists:employees,id',
            'statu' => 'required',
            'user' => 'required|string|max:191|unique:users,user,' . $userId,
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'employee_id.integer' => 'El funcionario seleccionado no es válido.',
            'employee_id.exists' => 'El funcionario seleccionado no existe.',
            'statu.required' => 'Elija el estado para el usuario.',
            'user.required' => 'Ingrese su Usuario.',
            'user.unique' => 'Ese nombre de usuario ya está en uso por otra cuenta.',
            'password.min' => 'La contraseña debe contener mínimo 6 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
