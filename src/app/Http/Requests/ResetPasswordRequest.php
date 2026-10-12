<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasMenuPermission('users', 'edit');
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses untuk menu ini.')
        );
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
