<?php

namespace App\Http\Requests;

use App\Models\Subtask;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubtaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Subtask::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'required|exists:cards,id',
            'subtask_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:to do,in progress,done',
            'estimated_hours' => 'nullable|decimal:5,2|min:0|max:999.99',
            'actual_hours' => 'nullable|decimal:5,2|min:0|max:999.99',
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
            'card_id.required' => 'Kartu wajib dipilih.',
            'card_id.exists' => 'Kartu yang dipilih tidak valid.',
            
            'subtask_name.required' => 'Nama subtask wajib diisi.',
            'subtask_name.string' => 'Nama subtask harus berupa teks.',
            'subtask_name.max' => 'Nama subtask tidak boleh lebih dari 255 karakter.',
            
            'description.string' => 'Deskripsi harus berupa teks.',
            
            'status.in' => 'Status harus salah satu dari: To Do, In Progress, atau Done.',
            
            'estimated_hours.decimal' => 'Estimasi jam harus berupa angka desimal.',
            'estimated_hours.min' => 'Estimasi jam tidak boleh bernilai negatif.',
            'estimated_hours.max' => 'Estimasi jam tidak boleh lebih dari 999.99 jam.',
            
            'actual_hours.decimal' => 'Jam aktual harus berupa angka desimal.',
            'actual_hours.min' => 'Jam aktual tidak boleh bernilai negatif.',
            'actual_hours.max' => 'Jam aktual tidak boleh lebih dari 999.99 jam.',
            
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
            'card_id' => 'kartu',
            'subtask_name' => 'nama subtask',
            'description' => 'deskripsi',
            'status' => 'status',
            'estimated_hours' => 'estimasi jam',
            'actual_hours' => 'jam aktual',
            'position' => 'posisi',
        ];
    }
}
