<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        return Auth::check() && $user instanceof \App\Models\User && $user->hasMenuPermission('users', 'create');
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
