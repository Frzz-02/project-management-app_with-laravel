<?php

namespace App\Http\Requests;

use App\Models\ProjectMember;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ProjectMember::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:team lead,developer,designer',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek yang dipilih tidak valid.',
            
            'user_id.required' => 'Pengguna wajib dipilih.',
            'user_id.exists' => 'Pengguna yang dipilih tidak valid.',
            
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus salah satu dari: Team Lead, Developer, atau Designer.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'project_id' => 'proyek',
            'user_id' => 'pengguna',
            'role' => 'role',
        ];
    }
}
