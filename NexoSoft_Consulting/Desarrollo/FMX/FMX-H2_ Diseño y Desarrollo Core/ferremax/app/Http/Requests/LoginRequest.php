<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CUS-01 Iniciar sesión: campos obligatorios (FA 3.2.4).
 * La interfaz valida antes de enviar; esta es la validación del servidor.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:60'],
            'password'       => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'Ingrese su nombre de usuario.',
            'nombre_usuario.max'      => 'El nombre de usuario no puede superar los 60 caracteres.',
            'password.required'       => 'Ingrese su contraseña.',
        ];
    }
}
