<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'            => 'nullable|string|max:150',
            'company_name'         => 'nullable|string|max:200',
            'email'                => 'nullable|email|unique:users,email,' . $this->id,
            'phone'                => 'nullable|string|max:30|unique:users,phone,' . $this->id,
            'ifu'                  => 'nullable|string|max:50',
            'adresse'              => 'nullable|string|max:255',
            'ville'                => 'nullable|string|max:120',
            'account_category_id'  => 'nullable|exists:account_categories,id',

            // ---------------------------------------------
            // DOCUMENTS (liste de fichiers)
            // ---------------------------------------------
            'documents_urls'       => 'nullable|array',
            'documents_urls.*'     => 'file|mimes:jpg,jpeg,png,pdf|max:4096',

            // ---------------------------------------------
            // AVATAR
            // ---------------------------------------------
            'avatar_url'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // ---------------------------------------------
            // Compte Actif ou non
            // ---------------------------------------------
            'activated' => ['nullable', 'in:0,1,true,false'],


            // ---------------------------------------------
            // PASSWORD
            // ---------------------------------------------
            'current_password'     => 'nullable|string|min:8',
            'password'             => 'nullable|string|min:8|confirmed',
        ];
    }
}
