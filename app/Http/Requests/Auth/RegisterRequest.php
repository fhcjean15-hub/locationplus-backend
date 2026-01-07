<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name'            => 'nullable|string|max:150',
            'company_name'         => 'nullable|string|max:200',
            'email'                => 'required|email|unique:users,email',
            'phone'                => 'nullable|string|max:30|unique:users,phone',
            'password'             => 'required|string|min:6',
            'account_type'         => 'required|in:particulier,entreprise,admin',
            'account_category_id'  => 'nullable|exists:account_categories,id',
        ];
    }
}
