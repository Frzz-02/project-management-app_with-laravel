<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after_or_equal:today',
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
            'project_name.required' => 'Nama proyek wajib diisi.',
            'project_name.string' => 'Nama proyek harus berupa teks.',
            'project_name.max' => 'Nama proyek tidak boleh lebih dari 255 karakter.',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            
            'deadline.required' => 'Tanggal deadline wajib diisi.',
            'deadline.date' => 'Tanggal deadline harus berupa format tanggal yang valid.',
            'deadline.after_or_equal' => 'Tanggal deadline tidak boleh sebelum hari ini.',
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
            'project_name' => 'nama proyek',
            'description' => 'deskripsi',
            'deadline' => 'tanggal deadline',
        ];
    }
}
