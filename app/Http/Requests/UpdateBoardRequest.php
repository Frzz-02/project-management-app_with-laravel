<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBoardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('board'));
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
            'board_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
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
            
            'board_name.required' => 'Nama board wajib diisi.',
            'board_name.string' => 'Nama board harus berupa teks.',
            'board_name.max' => 'Nama board tidak boleh lebih dari 255 karakter.',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            
            'position.integer' => 'Posisi harus berupa angka.',
            'position.min' => 'Posisi tidak boleh bernilai negatif.',
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
            'board_name' => 'nama board',
            'description' => 'deskripsi',
            'position' => 'posisi',
        ];
    }
}
