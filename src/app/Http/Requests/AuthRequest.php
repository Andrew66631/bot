<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    public function rules(): array
    {
        $route = $this->route()->getName();

        return match($route) {
            'auth' => [
                'phone' => 'required|string|min:10|max:15'
            ],
            'auth.confirm' => [
                'code' => 'required|string|min:4|max:6',
                'phone_code_hash' => 'sometimes|string'
            ],
            default => []
        };
    }
}
